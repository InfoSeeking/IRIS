#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "prefixtree.h"
#include "text_processing.h"
#include "search_blocks.h"

/*
each word from the wordList in the prefix tree has an increasing id, which the matches array refers to
*/
char ** getBlocks(char *data, pnode *words_tree, int num_words, int searchWindow, int resultWindow, int useStemming, int *num_matches){
	int index = 0;
	char * word = getWord(data, &index);
	int * matches = (int *)malloc(sizeof(int) * num_words);
	int i;
	for(i = 0; i < num_words; i++){
			matches[i] = 0;
	}

	//printf("Max result window: %d\n", resultWindow * 2);
	bll *resultLL = makeBLL(resultWindow + searchWindow); //other half is in front, will be printed while ignoring
	bll *matchLL = makeBLL(searchWindow);

	int num_block_matches = 10;
	int curMatch = -1;
	char ** blockMatches = (char **)malloc(sizeof(char *) * num_block_matches);
	
	int ignore = 0;

	while(word != NULL){
		pnode *n = fetchFromPrefix(words_tree, word, useStemming);
		int match = 0;
		if(n != NULL){
			if(useStemming){
				if(n->count > 0){
					//stem match
					match = 1;
				}
			}
			else{
				//total match
				match = 1;
			}
		}
		int word_id = -1;
		if(match){
			//printf("Match for word: %s\n", word);
			//printf("Word id %d\n", n->id);
			word_id = n->id;
			(matches[n->id])++;
		}
		bnode * removed = addToRearBLL(matchLL, word, word_id);

		if(removed != NULL){
			if(removed->id != -1){
				//then this word was a match, must update matches array
				(matches[removed->id])--;
			}
		}
		removed = addToRearBLL(resultLL, word, word_id);
		if(removed != NULL){ 
			free(removed->word);
			free(removed);
		}
		//check for block match
		int blockMatch = 1;
		
		//printf("START\n");
		for(i = 0; i < num_words; i++){
			//printf("Matches[%d] = %d\n", i, matches[i]);
			if(matches[i] == 0) blockMatch = 0;
		}
		//printf("END\n");

		if(blockMatch){
			(*num_matches)++;
			curMatch++;
			if(curMatch + 1 > num_block_matches){
				//resize
				num_block_matches *= 2;
				blockMatches = (char **)realloc(blockMatches, sizeof(char *) * num_block_matches);
			}

			//allocate space for block match, add all of the current words on the resultList
			ignore = resultWindow + searchWindow;
			//skip for searchWindow number of words to ignore multiple matches on same content and to add the rest of the words to the curMatch

			//clear and free both linked lists
			bnode * ptr = resultLL->rear->next;

			int strSize = resultLL->num_chars * 2;//approximate
			int actualSize = 0;
			blockMatches[curMatch] = (char *)malloc(strSize * sizeof(char));
			do{
				actualSize += strlen(ptr->word) + 1;
				if(actualSize > strSize){
					strSize *= 2;
					blockMatches[curMatch] = (char *)realloc(blockMatches[curMatch], strSize * sizeof(char));
				}
				strcat(blockMatches[curMatch], ptr->word);
				strcat(blockMatches[curMatch], " ");
				ptr = ptr->next;
			}while(ptr != resultLL->rear->next);

			//add other half of words
			word = getWord(data, &index);
			for(i = 0; i < resultWindow + searchWindow && word != NULL; i++){
				actualSize += strlen(word) + 1;
				if(actualSize > strSize){
					strSize *= 2;
					blockMatches[curMatch] = (char *)realloc(blockMatches[curMatch], strSize * sizeof(char));
				}
				strcat(blockMatches[curMatch], word);
				strcat(blockMatches[curMatch], " ");
				word = getWord(data, &index);
			}

			//printf("Block data: \n %s\n", blockMatches[curMatch]);
			for(i = 0; i < num_words; i++){
				matches[i] = 0;
			}

			//free and clear
			resultLL->rear = NULL;
			matchLL->rear = NULL;
			if(word == NULL){
				break;
			}

		}
		
		//printBLL(resultLL);
		word = getWord(data, &index);
	}
	//printBLL(resultLL);
	return blockMatches;
}

bll * makeBLL(int max_num_words){
	bll * newLL = (bll *)malloc(sizeof(bll));
	newLL->rear = NULL;
	newLL->num_words = 0;
	newLL->max_num_words = max_num_words;
	return newLL;
}
//adds to bll, if the number of words is greater than the max, it will remove the last one and return it
//id is -1 if this word is not a match
bnode * addToRearBLL(bll * list, char * word, int id){
	bnode * rear = list->rear;
	bnode * newNode = (bnode *)malloc(sizeof(bnode));
	newNode->word = word;
	newNode->id = id;
	list->num_words++;
	list->num_chars += strlen(word);

	if(rear == NULL){
		//nothing added to list yet
		list->rear = newNode;
		list->rear->next = list->rear;
		return NULL;
	}
	
	newNode->next = rear->next;
	rear->next = newNode;
	list->rear = newNode;

	if(list->num_words > list->max_num_words){
		//printf("Removing\n");
		//remove front node
		bnode * front = newNode->next;//node to remove
		list->num_chars -= strlen(front->word);
		list->num_words--;
		newNode->next = front->next;
		front->next = NULL;
		return front;
	}

	return NULL;
}

void printBLL(bll *list){
	if(list->rear == NULL){
		return;
	}
	bnode * ptr = list->rear->next;
	printf("LL START\n");
	do{
		printf("Word: %s\n", ptr->word);
		ptr = ptr->next;
	}while(ptr != list->rear->next);
	printf("LL END\n");
}