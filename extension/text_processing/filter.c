#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "hashtable.h"
#include "text_processing.h"

void filter_words(char *input_data, char *stopwords_data){
    int index = 0;
    char * word = getWord(stopwords_data, &index);
    hashtable *table = (hashtable *)malloc(sizeof(hashtable));
    //build hash table of stopwords
    table->buckets = 0;
    rehash(table, 100);
    while(word != NULL){
        addToHash(table, word);
        word = getWord(stopwords_data, &index);
    }

    //go through words of input file, ignore words in table and print it out
    int first = 1;
    index = 0;
    word = getWord(input_data, &index);
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
        word = getWord(input_data, &index);
    }
    freeHash(table, 1);
}
