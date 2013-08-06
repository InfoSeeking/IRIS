#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "prefixtree.h"
#include "text_processing.h"

/*
    stopwords_data - null if not checking
    minlength - -1 if not checking
    maxlength - -1 if not checking
*/
void filter_words(char *input_data, char *stopwords_data, int minlength, int maxlength, int useStemming, int useNumbers){
    int index = 0;
    pnode ptree = makePTree();
    int hasPTree = 0;

    if(stopwords_data != NULL){
        hasPTree = 1;
        char * word = getWord(stopwords_data, &index);
        while(word != NULL){
            addToPrefix(&ptree, word);
            free(word);
            word = getWord(stopwords_data, &index);
        }
    }

    //go through words of input file, ignore words in table and print it out
    int first = 1;
    index = 0;
    char * word = getWordOfSentence(input_data, &index, NULL, useNumbers);
    while(word != NULL){

        if((hasPTree == 0 || fetchFromPrefix(&ptree, word, useStemming) == NULL) && (minlength == -1 || strlen(word) >= minlength) && (maxlength == -1 || strlen(word) <= maxlength)){
            if(first){
                first = 0;
            }
            else{
                printf(" ");
            }     
            printf("%s", word);
        }
        free(word);
        word = getWord(input_data, &index);
    }
    if(hasPTree == 1){
        freeTree(&ptree);
    }
}
