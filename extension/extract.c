#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "hashtable.h"
#include "extract.h"
#include "text_processing.h"


//sticking with hashtable for now because prefix tree can only (easily) be traversed recursively, and that would not be simple to make the sorted array...
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
keyword ** extract_keywords(char * data, int *num_words){
    hashtable *table = (hashtable *)malloc(sizeof(hashtable));
    double totalWords = 0;
    int uniqueWords = 0;

    table->buckets = 0;//initialize node pointers to nothing
    rehash(table, 100);

    int index = 0;
    char * word = getWord(data, &index);
    while(word != NULL){
        totalWords++;
        int added = addToHash(table, word);
        uniqueWords += added;
        if(added == 0){
            //word was duplicate, can free
            free(word);
        }
        word = getWord(data, &index);
    }

    keyword ** sorted = findFrequencies(table, totalWords, uniqueWords);

    *num_words = (int)uniqueWords;
    freeHash(table, 0);
    return sorted;
}