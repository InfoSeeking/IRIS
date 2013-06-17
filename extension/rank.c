#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "hashtable.h"
#include "extract.h"
#include "text_processing.h"
#include "rank.h"
/*
returns a list of pointers to keyword structs sorted by frequency in descending order
*/
double get_sum(char * data, char *words_data){
    hashtable *table = (hashtable *)malloc(sizeof(hashtable));
    double totalWords = 0;
    int uniqueWords = 0;

    table->buckets = 0;//initialize node pointers to nothing
    rehash(table, 100);

    int index = 0;
    char * word = getWord(data, &index);
    while(word != NULL){
        totalWords++;
        uniqueWords += addToHash(table, word);
        word = getWord(data, &index);
    }

    findFrequencies(table, totalWords, uniqueWords);

    index = 0;
    double freqSum = 0;
    word = getWord(words_data, &index);
    while(word != NULL){
        keyword * k = fetchFromHash(table, word);
        if(k != NULL){
            freqSum += k->freq;
        }
        word = getWord(words_data, &index);
    }
    freeHash(table, 1);
    return freqSum;
}