#include "text_processing.h"
#include "extract.h"
#include "hashtable.h"
#include "summarize.h"
#include <stdlib.h>
#include <stdio.h>
#include <string.h>

r_sent * getAndRankSentence(char *data, int *index, hashtable *rankedWords){
	int endOfSentence = 0;
	double rank = 0;
    char * word = getWordOfSentence(data, index, &endOfSentence);
    int sent_size = 30;
    char * sent = (char *)malloc(sizeof(char) * sent_size);
    sent[0] = '\0';
    int firstWord = 1;
    //create sent object
    r_sent *tmp = (r_sent *)malloc(sizeof(r_sent));
    if(word == NULL){
    	return NULL;
    }
    while(word != NULL){
        keyword *k = fetchFromHash(rankedWords, word);

        while(strlen(sent) + strlen(word) + 1 > sent_size){ //+1 for space
        	//double size
        	sent_size *= 2;
        	sent = (char *)realloc(sent, sizeof(char) * sent_size);
        }
        if(!firstWord){
        	strcat(sent, " ");
        }
        else{
        	firstWord = 0;
        }
        strcat(sent, word);
        if(k != NULL){
			rank += k->freq;
        }
        if(endOfSentence){
            tmp->rank = rank;
            tmp->sent = sent;
            return tmp;
        }
        word = getWordOfSentence(data, index, &endOfSentence);
    }
    tmp->rank = rank;
    tmp->sent = sent;
    return tmp;
}

/* num_sents is the number of sentences to return */
char * summarize(char * data, char * words_data, int num_sents){
    //first rank the words
    int num_words;
	hashtable *table = buildTable(words_data, &num_words, NULL);//should store this
    //then rank the sentences
    int index = 0;
    int list_size = 10;//number of total sentences to create sorted list
    int sents_found = 0;//number of sentences found
    r_sent **sorted = (r_sent **)malloc(sizeof(r_sent *) * list_size);
    r_sent *rs = getAndRankSentence(data, &index, table);
    while(rs != NULL){
    	sents_found++;
    	if(sents_found > list_size){
    		list_size *= 2;
    		sorted = (r_sent **)realloc(sorted, sizeof(r_sent *) * list_size);
    	}
    	int j = sents_found - 1;
    	sorted[j] = rs;
    	while(j > 0 && sorted[j-1]->rank < sorted[j]->rank){
    		r_sent *tmp = sorted[j-1];
    		sorted[j-1] = sorted[j];
    		sorted[j] = tmp;
    		j--;
    	}
    	rs = getAndRankSentence(data, &index, table);
    }
    freeHash(table, 1);
    int i;
    int upper_bound = sents_found < num_sents ? sents_found : num_sents;
    int out_size = 100;
    char * output = (char *)malloc(sizeof(char) * out_size);
    output[0] = '\0';
    for(i = 0; i < upper_bound; i++){
    	while(strlen(output) + strlen(sorted[i]->sent) + 2 > out_size){//+2 for.\n
    		out_size *= 2;
    		output = (char *)realloc(output, sizeof(char) * out_size);
    	}
    	strcat(output, ".\n");
    	strcat(output, sorted[i]->sent);
    }
    return output;
}