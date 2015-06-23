#ifndef EXTRACT_H
#define EXTRACT_H
#include "hashtable.h"

keyword ** findFrequencies(hashtable *table, double totalWords, int uniqueWords);
keyword ** extract_keywords(char * data, int *num_words);
hashtable * buildTable(char * data, int *num_words, keyword *** sorted);

#endif