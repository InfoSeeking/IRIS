#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "hashtable.h"
#include "text_processing.h"

/* 
this is a boolean match query, it returns true if the data matches the constraints
*/
short processQuery(char * data, char * words_data, char * type, int val){
	
	int index = 0;
    hashtable *table = NULL;

    if(data != NULL){
        char * word = getWord(data, &index);
        table = (hashtable *)malloc(sizeof(hashtable));
        //build hash table of words, so we can match them
        table->buckets = 0;
        rehash(table, 100);
        while(word != NULL){
            addToHash(table, word);
            word = getWord(data, &index);
        }
    }
    else{
    	return 0;
    }

    //read the words from the words_data
    index = 0;
    char * word = getWord(words_data, &index);
    while(word != NULL){
    	keyword * kword = fetchFromHash(table, word);
    	int count = 0;
    	if(kword != NULL){
    		//keyword is in the table
    		count = kword->count;
    	}
		if(strcmp(type, "eq") == 0){
			if(count != val){
				freeHash(table, 1);
				return 0;
			}
		}
		else if(strcmp(type, "ne") == 0){
			if(count == val){
				freeHash(table, 1);
				return 0;
			}
		}
		else if(strcmp(type, "gt") == 0){
			if(count <= val){
				freeHash(table, 1);
				return 0;
			}
		}
		else if(strcmp(type, "lt") == 0){
			if(count >= val){
				freeHash(table, 1);
				return 0;
			}
		}
		//at this point, we are still good, and there is still a possibility to be true
    	word = getWord(words_data, &index);

    }
    //I WANT TO BE FREE
    freeHash(table, 1);
    return 1;
}