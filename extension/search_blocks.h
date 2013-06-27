#ifndef SEARCH_BLOCKS_H
#define SEARCH_BLOCKS_H
#include "prefixtree.h"


typedef struct bnode_s{
	char *word;
	int id;
	struct bnode_s *next;
} bnode;

typedef struct bll_s{
	bnode *rear;
	int num_words;
	int num_chars;
	int max_num_words;
} bll;

bnode * addToRearBLL(bll * list, char * word, int id);
void printBLL(bll *list);
char ** getBlocks(char *data, pnode *words_tree, int num_words, int searchWindow, int resultWindow, int useStemming, int * num_matches);
bll * makeBLL(int max_num_words);
#endif