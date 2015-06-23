#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <ctype.h>
#include "prefixtree.h"

int getIndex(char c){
	int index;
	if(isdigit(c)){
		index = c - '0';
	}
	else{
		//assume alpha
		index = c - 'a' + 10;
	}
	if(index > 35 || index < 0){
		return -1;
	}
	return index;
}
pnode makePTree(){
	pnode ptree;
	ptree.children = NULL;
	ptree.count = 0;
	ptree.prefixCount = 0;
	return ptree;
}

pnode * makeChildren(){
	int size = 10 + 26;
	pnode * children = (pnode *)malloc(sizeof(pnode) * size);
	int i;
	for(i = 0; i < size; i++){
		children[i].children = NULL;
		children[i].count = 0;
		children[i].prefixCount = 0;
	}
	return children;
}

//returns 1 on error
int addToPrefix(pnode *root, char * word){
	return addToPrefixWithId(root, word, 0);
}
//returns 1 on error
int addToPrefixWithId(pnode *root, char * word, int id){
	pnode *ptr = root;
	pnode *prev = NULL;

	if(strlen(word) == 0){
		return 1;
	}
	int i;
	int index;
	for(i = 0; i < strlen(word); i++){
		if(ptr->children == NULL){
			ptr->children = makeChildren();
		}
		index = getIndex(word[i]);
		if(index == -1){
			return 1;
		}

		ptr->children[index].prefixCount++;
		prev = ptr;
		ptr = &(ptr->children[index]);
	}
	prev->children[index].count++;
	prev->children[index].id = id;
	return 0;
}

//fetches total match
//if useStemming is equal to 1, it will give the best match it can get if it doesn't find an exact match
pnode * fetchFromPrefix(pnode *root, char * word, int useStemming){
	pnode *ptr = root;
	pnode *prev = NULL;
	int i;
	int index;
	if(strlen(word) == 0){
		return NULL;
	}

	for(i = 0; i < strlen(word); i++){
		if(ptr->children == NULL){
			return NULL;
		}
		index = getIndex(word[i]);
		prev = ptr;
		ptr = &(ptr->children[index]);
		if(useStemming){
			if(ptr->count > 0){
				return ptr;
			}
		}
	}
	if(ptr->count > 0){
		return ptr;
	}
	else{
		//dangerous
		return NULL;
	}
} 

void freeTree(pnode *tree){
	int i;
	if(tree->children == NULL){
		return;
	}
	for(i = 0; i < 36; i++){
		freeTree(&(tree->children[i]));
	}
	free(tree->children);
}


/* 
this is a boolean match query, it returns true if the data matches the constraints

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
*/