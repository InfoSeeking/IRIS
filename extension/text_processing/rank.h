#ifndef RANK_H
#define RANK_H

double get_sum(char * data, char *words_data);

typedef struct {
	double rank;
	char * content;
	char * id;
} rankedDoc;

#endif