#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "prefixtree.h"
#include "text_processing.h"

/* 
this is a boolean match query, it returns true if the data matches the constraints
*/
short processQuery(char * data, char * words_data, char * type, int val, int useStemming){
	
	int index = 0;
    pnode ptree = makePTree();

    if(data != NULL){
        char * word = getWord(data, &index);
        while(word != NULL){
            addToPrefix(&ptree, word);
            free(word);
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
    	pnode * kword = fetchFromPrefix(&ptree, word, 0);
    	int count = 0;
    	if(kword != NULL){
    		//keyword is in the table or at least it is a stem for other words
    		count = kword->count;
            if(useStemming){
                count = kword->prefixCount;
            }
    	}
		if(strcmp(type, "eq") == 0){
			if(count != val){
				freeTree(&ptree);
				return 0;
			}
		}
		else if(strcmp(type, "ne") == 0){
			if(count == val){
				freeTree(&ptree);
				return 0;
			}
		}
		else if(strcmp(type, "gt") == 0){
			if(count <= val){
				freeTree(&ptree);
				return 0;
			}
		}
		else if(strcmp(type, "lt") == 0){
			if(count >= val){
				freeTree(&ptree);
				return 0;
			}
		}
		//at this point, we are still good, and there is still a possibility to be true
        free(word);
    	word = getWord(words_data, &index);
        
    }
    freeTree(&ptree);
    return 1;
}