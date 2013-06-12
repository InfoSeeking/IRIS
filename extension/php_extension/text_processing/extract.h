#ifndef EXTRACT_H
#define EXTRACT_H

int getHashCode(char *c);
int addToHash(hashtable *table, char *c);
void rehash(hashtable *table, int size);
keyword ** findFrequencies(hashtable *table, double totalWords, int uniqueWords);
void freeLL(node *n);
void freeHash(hashtable *table);

#endif