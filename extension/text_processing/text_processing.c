#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "extract.h"
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

char * getWord(FILE *handle){
    int wordIndex = 0;
    int origWordSize = 10;
    int wordSize = origWordSize;
    char *word = (char *)malloc(sizeof(char) * wordSize);
    char c;
    short endOfWord = 0;
    short endOfFile = 0;
    while(1){
        c = (char)fgetc(handle);
        if(c == EOF){
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


void filter_words(char * filename, char * stopwordsfn){
    FILE *stophandle = fopen(stopwordsfn, "r");
    if(stophandle == NULL){
        err("Could not open stopwords file\n");
        return;
    }
    char * word = getWord(stophandle);
    hashtable *table = (hashtable *)malloc(sizeof(hashtable));
    //build hash table of stopwords
    table->buckets = 0;
    rehash(table, 100);
    while(word != NULL){
        addToHash(table, word);
        word = getWord(stophandle);
    }
    fclose(stophandle);
    
    FILE *handle = fopen(filename, "r");
    if(handle == NULL){
        err("Could not open input file\n");
        return;
    }

    //go through words of input file, ignore words in table and print it out
    int first = 1;
    word = getWord(handle);
    while(word != NULL){
        if(fetchFromHash(table, word) == NULL){
            if(first){
                first = 0;
            }
            else{
                printf(" ");
            }     
            printf("%s", word);
        }
        free(word);
        word = getWord(handle);
    }
    fclose(handle);
    freeHash(table);
}

int main(int argc, char **argv){
    char * fn;
    int num_words; //total unique words in doc
    int word_limit;//user supplied
    if(argc < 2){
        return err("Invalid args\n");
    }
    fn = argv[1];
    if(strcmp("extract", fn) == 0){
        if(argc != 4){
            return err("Invalid args for extract\n");
        }
        word_limit = atoi(argv[3]);
        keyword ** words = extract_keywords(argv[2], &num_words);

        int i;
        int upperBound = word_limit < num_words ? word_limit : num_words;
        for(i = 0; i < upperBound; i++){
            printf("%s,%f\n", words[i]->word, words[i]->freq);
            free(words[i]->word);
            free(words[i]);
        }
        free(words);
    }
    else if(strcmp("filter", fn) == 0){
        if(argc != 4){
            return err("Invalid args for filter\n");
        }
        //print words that are not in stopwords
        filter_words(argv[2], argv[3]);
    }

    return 0;
}