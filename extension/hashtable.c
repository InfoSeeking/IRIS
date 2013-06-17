#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "extract.h"
#include "hashtable.h"

int getHashCode(char *c){
    int i;
    int sum = 0;
    for(i = 0; i < strlen(c); i++){
        sum += c[i];
    }
    return sum;
}

/*
returns 1 if there is a new word added, 0 if not
*/
int addToHash(hashtable *table, char *c){
    int hashCode = getHashCode(c);
    int index = hashCode % table->size;
    //check if bucket has nodes already
    if(table->buckets[index] == 0){
        //empty bucket
        //add to bucket
        node *n = (node *)malloc(sizeof(node));
        n->data = (keyword *)malloc(sizeof(keyword));
        n->data->word = c;
        n->data->count = 1;
        n->next = 0;
        table->buckets[index] = n;
        return 1;
    }
    else{
        //check if it is already in bucket
        node *prev = 0;
        node *n;
        for(n = table->buckets[index]; n != 0; n = n->next){
            if(strcmp(n->data->word, c) == 0){
                (n->data->count)++;
                return 0; //done
            }
            else{
                prev = n;
            }
        }
        prev->next = (node *)malloc(sizeof(node));
        prev->next->data = (keyword *)malloc(sizeof(keyword));
        prev->next->data->word = c;
        prev->next->data->count = 1;
        prev->next->next = 0;
        return 1;
    }

}

void rehash(hashtable *table, int size){
    table->size = size;
    if(table->buckets == 0){
        table->buckets = (node **)malloc(size * sizeof(node*));
        int i;
        for(i = 0; i < size; i++){
            table->buckets[i] = 0;
        }
    }
    else{
        //todo
        //table->keywords = (keyword *)realloc(table->keywords, size * sizeof(keyword));  
    }
}

//recursively frees a linked list
void freeLL(node *n, int freeWords){
    if(n == 0){
        return;
    }
    else{
        freeLL(n->next, freeWords);
        if(freeWords){
            free(n->data);
        }
        free(n);
    }
}

void freeHash(hashtable *table, int freeWords){
    //todo
    int i;
    for(i = 0; i < table->size; i++){
        freeLL(table->buckets[i], freeWords);
    }
    free(table->buckets);
    free(table);
}

keyword * fetchFromHash(hashtable *table, char * c){
    int hashCode = getHashCode(c);
    int index = hashCode % table->size;
    //check if bucket has nodes already
    if(table->buckets[index] == 0){
        return NULL;
    }
    else{
        //check if it is already in bucket
        node *n;
        for(n = table->buckets[index]; n != 0; n = n->next){
            if(strcmp(n->data->word, c) == 0){
                return n->data;
            }
        }
        return NULL;
    }
}

void printTable(hashtable *table){
    int i;
    node * n;
    for(i = 0; i < table->size; i++){
        for(n = table->buckets[i]; n != 0; n = n->next){
            printf("Word: '%s'\n", n->data->word);
        }
    }
}