#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "hashtable.h"
#include "extract.h"
#include "text_processing.h"


keyword ** findFrequencies(hashtable *table, double totalWords, int uniqueWords){
    keyword **sorted = (keyword **)malloc(sizeof(keyword *) * uniqueWords);

    int count = 0;
    int i;
    for(i = 0; i < table->size; i++){
        node *n;
        for(n = table->buckets[i]; n != 0; n = n->next){
            n->data->freq = ((double)n->data->count) / totalWords;
           

            int j = count;
            sorted[j] = n->data;
            //insertion sort O(n^2), may replace with better algorithm later
            while(j > 0 && sorted[j]->freq > sorted[j-1]->freq){
                keyword *tmp = sorted[j-1];
                sorted[j-1] = sorted[j];
                sorted[j] = tmp;
                j--;
            }
            count++;
        }
    }

    return sorted;
}

/*
returns a list of pointers to keyword structs sorted by frequency in descending order
*/
keyword ** extract_keywords(char * filename, int *num_words){
    hashtable *table = (hashtable *)malloc(sizeof(hashtable));
    double totalWords = 0;
    int uniqueWords = 0;
    FILE *handle = fopen(filename, "r");

    table->buckets = 0;//initialize node pointers to nothing
    rehash(table, 100);

    if(handle == NULL){
        err("Could not open file");
        return NULL;
    }

    char * word = getWord(handle);
    while(word != NULL){
        totalWords++;
        uniqueWords += addToHash(table, word);
        word = getWord(handle);
    }

    keyword ** sorted = findFrequencies(table, totalWords, uniqueWords);

    *num_words = (int)uniqueWords;
    freeHash(table);
    fclose(handle);
    return sorted;
}