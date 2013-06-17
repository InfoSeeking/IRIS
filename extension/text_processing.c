#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <mxml.h>
#include "extract.h"
#include "filter.h"
#include "query.h"
#include "hashtable.h"
#include "rank.h"
#include "text_processing.h"

/*
issues:
-still some memory leaks, not sure where
-this would be a perfect case to use a prefix tree, so I may change this later, this will allow for checking words that start with other words, and would make
for better efficiency
*/
int err(char * message){
    fprintf(stderr, "%s", message);
    printHelp();
    return 1;
}

void printHelp(){
    printf("Usage: ./text_processing <function> <function args...>\n");
    printf("Functions:\n");
    printf("Extract:\n\textract <input xml file>\n");
    printf("\tExtract gives the most frequently appeared words, limited to the <number of words> parameter in order of frequency.\n\n");
    printf("Filter:\n\tfilter <input xml file>\n\n");
    printf("Query:\n\tquery <input xml file> (--words <words file>, --word-list \"<inline words>\" --match <eq <num>|gt <num>|lt <num>>)\n\n");
    printf("Rank:\n\trank <input xml file> (--words <words file>, --word-list \"<inline words>\"\n\n");
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
        if(*index + 1 > data_len){
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
    if(input_file == NULL){
        return NULL;
    }
    fseek(input_file, 0, SEEK_END);
    input_file_size = ftell(input_file);
    rewind(input_file);
    file_contents = malloc(input_file_size * (sizeof(char)));
    fread(file_contents, sizeof(char), input_file_size, input_file);
    fclose(input_file);
    return file_contents;
}

int main(int argc, char **argv){
    int index = 0;
    char * fn;
    int num_words; //total unique words in doc
    int word_limit;//user supplied
    if(argc == 2){
        if(strcmp(argv[1], "-h") == 0 || strcmp(argv[1], "--help")){
            printHelp();
            return 0;
        }
    }
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
        word_limit = 10;
        keyword ** words = extract_keywords(argv[2], &num_words);

        int i;
        int upperBound = word_limit < num_words ? word_limit : num_words;
        //iterate through every resource element, get the content, extract the words
        mxml_node_t *node;
        //first get the word limit
        node = mxmlFindElement(tree, tree, "numWords", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &word_limit);
        }

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
            printf("<content>%s</content>\n", mxmlGetOpaque(content));
            free(words);
            printf("</resource>\n");
        }
        free(words);
    }
    else if(strcmp("filter", fn) == 0){
        //go through arguments, get constraints
        int minlength = -1;
        int maxlength = -1;
        char * stopwords_data = NULL;
        int i;
        
        mxml_node_t *node;
        //get parameters
        node = mxmlFindElement(tree, tree, "stopWords", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            stopwords_data = (char *)mxmlGetOpaque(node);
        }
        node = mxmlFindElement(tree, tree, "minLength", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &minlength);
        }
        node = mxmlFindElement(tree, tree, "maxLength", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &maxlength);
        }

        //print words that are not in stopwords
        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource");
            if(id == NULL) return err("No id element found in resource");
             
            char *data = (char *)mxmlGetOpaque(content);
            
            int i;   
            printf("<resource>");
            printf("<id>%s</id>\n<content type=\"filtered\">\n", mxmlGetOpaque(id));
            filter_words(data, stopwords_data, minlength, maxlength);
            printf("</content>\n");
            printf("</resource>\n");
        }
    }
    else if(strcmp("query", fn) == 0){
        char * type;
        int val = -1;
        char * words_data = NULL;
        int i;
        //for each document, return the ones which match the query
        mxml_node_t *node;
        //get parameters
        node = mxmlFindElement(tree, tree, "wordList", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            words_data = (char *)mxmlGetOpaque(node);
        }
        node = mxmlFindPath(tree, "query/type");
        if(node != NULL){
            type = (char *)mxmlGetOpaque(node);
        }
        node = mxmlFindPath(tree, "query/value");
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &val);
        }

        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource");
            if(id == NULL) return err ("No id element found in resource");
             
            char *data = (char *)mxmlGetOpaque(content);
            if(processQuery(data, words_data, type, val)){
                printf("<resource>");
                printf("<id>%s</id>\n<content>\n", mxmlGetOpaque(id));
                printf("%s\n", data);
                printf("</content>\n");
                printf("</resource>\n");
            }
        }
    }
    else if(strcmp("rank", fn) == 0){
        //to do the ranking based on a set of words, we should check the freqencies of each word, sum them and then rank the documents based on that
        char * words_data = NULL;
        int i;
        //query
        for(i = 3; i < argc - 1; i++){ //don't check last one
            if(strcmp(argv[i], "--words") == 0){
                //get content of stopwords file
                words_data = readFile(argv[i+1]);
                if(words_data == NULL){
                    return err("Could not read words file\n");
                }
            }
            else if(strcmp(argv[i], "--word-list") == 0){
                //word list included in command line in csv
                words_data = argv[i+1];
            }
        }
        //for each document, return the ones which match the query
        mxml_node_t *node;
        //sorted document list by rank
        int num_docs = 5;//num of docs
        rankedDoc *ranked = (rankedDoc *)malloc(sizeof(rankedDoc) * num_docs);
        int cur_doc = 0;

        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource");
            if(id == NULL) return err ("No id element found in resource");
             
            char *data = (char *)mxmlGetOpaque(content);
            //get the sum of the frequencies 
            double sum = get_sum(data, words_data);

            rankedDoc r;
            r.rank = sum;
            r.content = (char *)mxmlGetOpaque(content);
            r.id = (char *)mxmlGetOpaque(id);

            int i = cur_doc;
            ranked[i] = r;
            while(i > 0 && ranked[i-1].rank < ranked[i].rank){
                //swap
                rankedDoc tmp = ranked[i-1];
                ranked[i-1] = ranked[i];
                ranked[i] = tmp;
            }
            cur_doc++;
            if(cur_doc >= num_docs){
                //increase size of array
                num_docs *= 2;
                ranked = (rankedDoc *)realloc(ranked, sizeof(rankedDoc) * num_docs);
            }
        }
        //print out list
        for(i = 0; i < cur_doc; i++){
            printf("<resource>");
            printf("<rank>%f</rank>", ranked[i].rank);
            printf("<id>%s</id>\n<content>\n", ranked[i].id);
            printf("%s\n", ranked[i].content);
            printf("</content>\n");
            printf("</resource>\n");
        }
        free(ranked);
    }
    mxmlDelete(tree);
    return 0;
}
