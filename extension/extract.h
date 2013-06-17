#ifndef EXTRACT_H
#define EXTRACT_H
#include "hashtable.h"

keyword ** findFrequencies(hashtable *table, double totalWords, int uniqueWords);
keyword ** extract_keywords(char * data, int *num_words);

#endif