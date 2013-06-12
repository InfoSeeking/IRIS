#ifndef HASH_TABLE_H
#define HASH_TABLE_H

typedef struct{
	char *word;
	int count;
	double freq;
} keyword;

typedef struct node_s{
	keyword *data;
	struct node_s *next;
} node;

typedef struct{
	node **buckets;
	int size;
} hashtable;

void rehash(hashtable *table, int size);
void freeLL(node *n);
void freeHash(hashtable *table);
int getHashCode(char *c);
int addToHash(hashtable *table, char *c);
keyword * fetchFromHash(hashtable *table, char * c);
void printTable(hashtable *table);

#endif