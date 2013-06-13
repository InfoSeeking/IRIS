#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <mxml.h>
#include "extract.h"
#include "filter.h"
#include "hashtable.h"
#include "text_processing.h"

/*
issues:
still some memory leaks, not sure where
*/
int err(char * message){
    fprintf(stderr, "%s", message);
    printHelp();
    return 1;
}

void printHelp(){
    printf("Usage: ./text_processing <function> <function args...>\n");
    printf("Functions:\n");
    printf("Extract:\n\textract <input file> <number of words>\n");
    printf("Filter:\n\tfilter <input file> <stopwords file>\n");
}

char * getWord(char *data, int *index){
    int data_len = strlen(data);
    int wordIndex = 0;
    int origWordSize = 10;
    int wordSize = origWordSize;
    char *word = (char *)malloc(sizeof(char) * wordSize);
    char c;
    short endOfWord = 0;
    short endOfFile = 0;

    while(1){
        if(*index + 1 >= data_len){
           //end of text, still need null character
            if(wordIndex == 0){
                //no end word
                return NULL;
            }
            else{
                c = '\0';
                endOfFile = 1;
                endOfWord = 1;
            }
        }
        else{
            c = data[*index];
            (*index)++;
        }
        if(c == ' ' || c == '\n' || c == ',' || c == '.' || c == ';' || c == ')' || c == '(' || c == '\r'){
            if(wordIndex == 0){
                //there is no beginning, keep waiting
                continue;
            }
            //end of word
            c = '\0';//null character
            endOfWord = 1;
        }
        
        if(wordIndex >= wordSize){
            //resize word buffer
            wordSize *= 2;
            word = (char *)realloc(word, sizeof(char) * wordSize);        
        }

        word[wordIndex] = c;
        wordIndex++;

        if(endOfWord == 1){
            //make lowercase
            int k;
            for(k = 0; k < strlen(word); k++){
                word[k] = tolower(word[k]);
            }
            return word;   
        }
        if(endOfFile){
            return NULL;
        }
    }
}

char * readFile(char * input_file_name){
    char *file_contents;
    long input_file_size;
    FILE *input_file = fopen(input_file_name, "rb");
    fseek(input_file, 0, SEEK_END);
    input_file_size = ftell(input_file);
    rewind(input_file);
    file_contents = malloc(input_file_size * (sizeof(char)));
    fread(file_contents, sizeof(char), input_file_size, input_file);
    fclose(input_file);
    return file_contents;
}

int main(int argc, char **argv){
    char * fn;
    int num_words; //total unique words in doc
    int word_limit;//user supplied
    if(argc < 3){ 
        //fname, function, input (possibly others)
        return err("Invalid args\n");
    }
    fn = argv[1]; //function
    //second argument should be xml file, so read it
    //get the content from the xml
    FILE *fp = fopen(argv[2], "r");
    if(fp == NULL){
        return err("Could not open xml file for reading\n");
    }
    mxml_node_t *tree;
    tree = mxmlLoadFile(NULL, fp, MXML_OPAQUE_CALLBACK);
    fclose(fp);
    if(tree == NULL){
        return err("Could not parse input xml\n");
    }
    
    if(strcmp("extract", fn) == 0){
        if(argc != 4){
            return err("Invalid args for extract\n");
        }
        word_limit = atoi(argv[3]);
        keyword ** words = extract_keywords(argv[2], &num_words);

        int i;
        int upperBound = word_limit < num_words ? word_limit : num_words;
        //iterate through every resource element, get the content, extract the words
        mxml_node_t *node;
        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource");
            if(id == NULL) return err ("No id element found in resource");
            
            
            char *data = (char *)mxmlGetOpaque(content);
            keyword ** words = extract_keywords(data, &num_words);
            int i;
            int upperBound = word_limit < num_words ? word_limit : num_words;
            
            printf("<resource>");
            printf("<id>%s</id>\n<keywords>\n", mxmlGetOpaque(id));
            for(i = 0; i < num_words; i++){
                if(i < upperBound){
                    printf("\t<keyword>\n\t\t<word>%s</word><freq>%f</freq>\n\t</keyword>\n", words[i]->word, words[i]->freq);
                }
                free(words[i]->word);
                free(words[i]);
            }
            printf("</keywords>\n");
            free(words);
            printf("</resource>\n");
        }
        free(words);
    }
    else if(strcmp("filter", fn) == 0){
        if(argc != 4){
            return err("Invalid args for filter\n");
        }
        //get content of stopwords file
        char * stopwords_data = readFile(argv[3]);
        //print words that are not in stopwords
        mxml_node_t *node;
        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource");
            if(id == NULL) return err ("No id element found in resource");
             
            char *data = (char *)mxmlGetOpaque(content);
            
            int i;   
            printf("<resource>");
            printf("<id>%s</id>\n<content type=\"filtered\">\n", mxmlGetOpaque(id));
            filter_words(data, stopwords_data);
            printf("</content>\n");
            printf("</resource>\n");
        }
    }
    mxmlDelete(tree);
    return 0;
}