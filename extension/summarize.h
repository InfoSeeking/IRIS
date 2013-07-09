#ifndef SUMMARIZE_H
#define SUMMARIZE_H
#include "hashtable.h"
typedef struct{
	char * sent;
	double rank;
} r_sent;//ranked sentence

r_sent * getAndRankSentence(char *data, int *index, hashtable *rankedWords);
char * summarize(char * data, char * words_data, int num_sents);


#endif