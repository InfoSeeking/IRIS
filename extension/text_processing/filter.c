#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "hashtable.h"
#include "text_processing.h"

/*
    stopwords_data - null if not checking
    minlength - -1 if not checking
    maxlength - -1 if not checking
*/
void filter_words(char *input_data, char *stopwords_data, int minlength, int maxlength){
    int index = 0;
    hashtable *table = NULL;

    if(stopwords_data != NULL){
        char * word = getWord(stopwords_data, &index);
        table = (hashtable *)malloc(sizeof(hashtable));
        //build hash table of stopwords
        table->buckets = 0;
        rehash(table, 100);
        while(word != NULL){
            addToHash(table, word);
            word = getWord(stopwords_data, &index);
        }
    }

    //go through words of input file, ignore words in table and print it out
    int first = 1;
    index = 0;
    char * word = getWord(input_data, &index);
    while(word != NULL){
        if((table == NULL || fetchFromHash(table, word) == NULL) && (minlength == -1 || strlen(word) >= minlength) && (maxlength == -1 || strlen(word) <= maxlength)){
            if(first){
                first = 0;
            }
            else{
                printf(" ");
            }     
            printf("%s", word);
        }
        free(word);
        word = getWord(input_data, &index);
    }
    if(table != NULL){
        freeHash(table, 1);
    }
}
