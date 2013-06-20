#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <math.h>
#include "hashtable.h"
#include "extract.h"
#include "text_processing.h"
#include "vector_rank.h"

vector_index * makeIndex(char * query){
    int index = 0;
    int totalWords = 0;
    int numWords = 10;//10 words for query
    char ** wordList = (char **)malloc(sizeof(char *) * numWords);
    char * word = getWord(query, &index);
    int i;

    while(word != NULL){
        wordList[totalWords] = word;
        totalWords++;
        if(totalWords >= numWords){
            //double
            numWords *= 2;
            wordList = realloc(wordList, numWords * sizeof(char *));
        }
        word = getWord(query, &index);
    }
    totalWords--;
    //now make the index linked lists
    vector_index * vi = (vector_index *)malloc(sizeof(vector_index));
    vi->lls = (vi_ll **)malloc(totalWords * sizeof(vi_ll *));
    vi->num_terms = totalWords;

    for(i = 0; i < totalWords; i++){
        vi->lls[i] = (vi_ll *)malloc(sizeof(vi_ll));
        vi->lls[i]->root = NULL;
        vi->lls[i]->num_docs = 0;
        vi->lls[i]->term = wordList[i];
    }
    free(wordList);
    return vi;
}
//recursively frees a linked list
void freeIndexLL(vi_node *n){
    if(n == 0){
        return;
    }
    else{
        freeIndexLL(n->next);

        free(n);
    }
}

void freeIndex(vector_index *vi, int freeWords){
    int i;
    for(i = 0; i < vi->num_terms; i++){
        freeIndexLL(vi->lls[i]->root);
        if(freeWords){
            free(vi->lls[i]->term);
        }
        free(vi->lls[i]);
    }
    free(vi->lls);
    free(vi);
}

void addToLinkedList(vi_ll * ll, int doc_id, int count){
    vi_node * newNode = (vi_node *)malloc(sizeof(vi_node));
    newNode->doc_id = doc_id;
    newNode->term_count = count;

    //add to beginning for ease (works even if ll->root == NULL)
    newNode->next = ll->root;
    ll->root = newNode;
    ll->num_docs++;
}

int getCount(vi_ll *ll, int doc_id){
    vi_node * n = ll->root;
    while(n != NULL && n->doc_id != doc_id){
        n = n->next;
    }
    if(n == NULL){
        return 0;
    }
    else{
        return n->term_count;
    }
}

void addToIndex(vector_index *vi, char * doc_data, int doc_id){
    hashtable *table = (hashtable *)malloc(sizeof(hashtable));
    double totalWords = 0;
    int uniqueWords = 0;
    int i;

    table->buckets = 0;//initialize node pointers to nothing
    rehash(table, 100);

    int index = 0;
    char * word = getWord(doc_data, &index);
    while(word != NULL){
        totalWords++;
        uniqueWords += addToHash(table, word);
        word = getWord(doc_data, &index);
    }

    findFrequencies(table, totalWords, uniqueWords);

    //now go through each term and add to index with count
    for(i = 0; i < vi->num_terms; i++){
        //get the number of times this word appears in the document
        keyword * k = fetchFromHash(table, vi->lls[i]->term);
        int count = 0;
        if(k != NULL){
            count = k->count;
        }
        if(count > 0){
            //add to linked list
            addToLinkedList(vi->lls[i], doc_id, count);
        }
    }
    freeHash(table, 1);
    return;
}

double dotProduct(double * v1, double * v2, int num_terms){
    double total = 0;
    int i;
    for(i = 0; i < num_terms; i++){
        total += v1[i] * v2[i];
    }
    return total;
}

double mag(double * v1, int num_terms){
    return sqrt(dotProduct(v1, v1, num_terms));
}