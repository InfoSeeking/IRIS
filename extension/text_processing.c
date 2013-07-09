#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <mxml.h>
#include <math.h>
#include "extract.h"
#include "filter.h"
#include "query.h"
#include "hashtable.h"
#include "prefixtree.h"
#include "rank.h"
#include "text_processing.h"
#include "vector_rank.h"
#include "search_blocks.h"

/*
issues:
-still some memory leaks, not sure where
-this would be a perfect case to use a prefix tree, so I may change this later, this will allow for checking words that start with other words, and would make
for better efficiency
*/
int err(char * message){
    fprintf(stderr, "%s", message);
    printHelp();
    return 1;
}

void printHelp(){
    printf("Usage: ./text_processing <function> <function args...>\n");
    printf("Functions:\n");
    printf("Extract:\n\textract <input xml file>\n");
    printf("\tExtract gives the most frequently appeared words, limited to the <number of words> parameter in order of frequency.\n\n");
    printf("Filter:\n\tfilter <input xml file>\n\n");
    printf("Query:\n\tquery <input xml file>\n\n");
    printf("Rank:\n\trank <input xml file>\n\n");
    printf("Vector Rank:\n\tvector_rank <input xml file>\n");
    printf("\tVector Rank uses a vector model to rank the documents against the supplied wordList query and ranks based off of the cosine similarity of the vectors formed\n\n");
    printf("Block Search:\n\tblock_search <input xml file>\n");
    printf("\t Block search searches for words supplied in the wordList and finds results within a searchWindow, and returns the nearest block of text within the resultWindow\n\n");
}

char * getWord(char *data, int *index){
    return getWordOfSentence(data, index, NULL);
}

//if endOfSentence is not null, it will be 1 when period reached
char * getWordOfSentence(char * data, int *index, int *endOfSentence){
    int data_len = strlen(data);
    int wordIndex = 0;
    int origWordSize = 10;
    int wordSize = origWordSize;
    char *word = (char *)malloc(sizeof(char) * wordSize);
    char c;
    short endOfWord = 0;
    short endOfFile = 0;
    if(endOfSentence != NULL){
        *endOfSentence = 0;
    }
    while(1){
        if(*index + 1 > data_len){
           //end of text, still need null character
            if(wordIndex == 0){
                //no end word
                return NULL;
            }
            else{
                c = '\0';
                endOfFile = 1;
                endOfWord = 1;
                if(endOfSentence != NULL){
                    *endOfSentence = 1;
                }
            }
        }
        else{
            c = data[*index];
            (*index)++;
        }
        if(c == ' ' || c == '\n' || c == ',' || c == '.' || c == ';' || c == ')' || c == '(' || c == '\r' || c == '\t'){
            if(wordIndex == 0){
                //there is no beginning, keep waiting
                continue;
            }
            //end of word
            if(c == '.' && endOfSentence != NULL){
                *endOfSentence = 1;
            }
            c = '\0';//null character
            endOfWord = 1;
        }
        
        if(wordIndex >= wordSize){
            //resize word buffer
            wordSize *= 2;
            word = (char *)realloc(word, sizeof(char) * wordSize);        
        }

        word[wordIndex] = c;
        wordIndex++;

        if(endOfWord == 1){
            //make lowercase
            int k;
            for(k = 0; k < strlen(word); k++){
                word[k] = tolower(word[k]);
            }
            return word;   
        }
        if(endOfFile){
            return NULL;
        }
    }
}

char * getSentence(char * data, int *index){
    int endOfSentence = 0;
    char * word = getWordOfSentence(data, index, &endOfSentence);
    while(word != NULL){
        printf("%s ", word);
        if(endOfSentence){
            printf("\n");
        }
        word = getWordOfSentence(data, index, &endOfSentence);
    }
}

char * readFile(char * input_file_name){
    char *file_contents;
    long input_file_size;
    FILE *input_file = fopen(input_file_name, "rb");
    if(input_file == NULL){
        return NULL;
    }
    fseek(input_file, 0, SEEK_END);
    input_file_size = ftell(input_file);
    rewind(input_file);
    file_contents = malloc(input_file_size * (sizeof(char)));
    fread(file_contents, sizeof(char), input_file_size, input_file);
    fclose(input_file);
    return file_contents;
}

int main(int argc, char **argv){
    int index = 0;
    char * fn;
    int num_words; //total unique words in doc
    int word_limit;//user supplied
    if(argc == 2){
        if(strcmp(argv[1], "-h") == 0 || strcmp(argv[1], "--help")){
            printHelp();
            return 0;
        }
    }
    if(argc < 3){ 
        //fname, function, input (possibly others)
        return err("Invalid args\n");
    }
    fn = argv[1]; //function
    //second argument should be xml file, so read it
    //get the content from the xml
    FILE *fp = fopen(argv[2], "r");
    if(fp == NULL){
        return err("Could not open xml file for reading\n");
    }
    mxml_node_t *tree;
    tree = mxmlLoadFile(NULL, fp, MXML_OPAQUE_CALLBACK);
    fclose(fp);
    if(tree == NULL){
        return err("Could not parse input xml\n");
    }
    
    if(strcmp("summarize", fn) == 0){
        char * words_data;
        mxml_node_t *node;
        node = mxmlFindElement(tree, tree, "wordList", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            words_data = (char *)mxmlGetOpaque(node);
        }

        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource\n");
            if(id == NULL) return err ("No id element found in resource\n");
            
            
            char *data = (char *)mxmlGetOpaque(content);
            char * summary = summarize(data, words_data, 10);//for now magic num

            printf("<resource>");
            printf("<id>%s</id>\n", mxmlGetOpaque(id));
            printf("<content type='summarized'>%s</content>\n", summary);
            printf("</resource>\n");
        }
    }
    else if(strcmp("extract", fn) == 0){
        word_limit = 10;
        keyword ** words = extract_keywords(argv[2], &num_words);

        int i;
        int upperBound = word_limit < num_words ? word_limit : num_words;
        //iterate through every resource element, get the content, extract the words
        mxml_node_t *node;
        //first get the word limit
        node = mxmlFindElement(tree, tree, "numWords", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &word_limit);
        }

        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource\n");
            if(id == NULL) return err ("No id element found in resource\n");
            
            
            char *data = (char *)mxmlGetOpaque(content);
            keyword ** words = extract_keywords(data, &num_words);
            int i;
            int upperBound = word_limit < num_words ? word_limit : num_words;
            
            printf("<resource>");
            printf("<id>%s</id>\n<keywords>\n", mxmlGetOpaque(id));
            for(i = 0; i < num_words; i++){
                if(i < upperBound){
                    printf("\t<keyword>\n\t\t<word>%s</word><freq>%f</freq>\n\t</keyword>\n", words[i]->word, words[i]->freq);
                }
                
            }
            printf("</keywords>\n");
            printf("<content type='extracted'>");
            for(i = 0; i < num_words; i++){
                printf("%s ", words[i]->word);
                free(words[i]->word);
                free(words[i]);
            }
            printf("</content>\n");
            free(words);
            printf("</resource>\n");
        }
        free(words);
    }
    else if(strcmp("filter", fn) == 0){
        //go through arguments, get constraints
        int minlength = -1;
        int maxlength = -1;
        char * stopwords_data = NULL;
        int i;
        int useStemming = 0;

        mxml_node_t *node;
        //get parameters
        node = mxmlFindElement(tree, tree, "wordList", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            stopwords_data = (char *)mxmlGetOpaque(node);
        }
        node = mxmlFindElement(tree, tree, "minLength", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &minlength);
        }
        node = mxmlFindElement(tree, tree, "maxLength", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &maxlength);
        }
        node = mxmlFindElement(tree, tree, "useStemming", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            char * us = (char *)mxmlGetOpaque(node);
            if(strcmp(us, "TRUE") == 0 || strcmp(us, "true") == 0){
                useStemming = 1;
            }
        }

        //print words that are not in stopwords
        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource\n");
            if(id == NULL) return err("No id element found in resource\n");
             
            char *data = (char *)mxmlGetOpaque(content);
            int ia = 0;
            getSentence(data, &ia);
            int i;   
            printf("<resource>");
            printf("<id>%s</id>\n<content type=\"filtered\">\n", mxmlGetOpaque(id));
            filter_words(data, stopwords_data, minlength, maxlength, useStemming);
            printf("</content>\n");
            printf("</resource>\n");
        }
    }
    else if(strcmp("query", fn) == 0){
        char * type = NULL;
        int val = -1;
        char * words_data = NULL;
        int i;
        int useStemming = 0;
        //for each document, return the ones which match the query
        mxml_node_t *node;
        //get parameters
        node = mxmlFindElement(tree, tree, "wordList", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            words_data = (char *)mxmlGetOpaque(node);
        }
        mxml_node_t * qnode = mxmlFindElement(tree, tree, "query", NULL, NULL, MXML_DESCEND);
        node = mxmlFindElement(tree, qnode, "type", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            type = (char *)mxmlGetOpaque(node);
        }
        else{
            return err("Query type required\n");
        }

        node = mxmlFindElement(tree, qnode, "value", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &val);
        }
        else{
            return err("Query value required\n");
        }

        node = mxmlFindElement(tree, tree, "useStemming", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            char * us = (char *)mxmlGetOpaque(node);
            if(strcmp(us, "TRUE") == 0 || strcmp(us, "true") == 0){
                useStemming = 1;
            }
        }
        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource\n");
            if(id == NULL) return err ("No id element found in resource\n");
             
            char *data = (char *)mxmlGetOpaque(content);
            if(processQuery(data, words_data, type, val, useStemming)){
                printf("<resource>");
                printf("<id>%s</id>\n<content>\n", mxmlGetOpaque(id));
                printf("%s\n", data);
                printf("</content>\n");
                printf("</resource>\n");
            }
        }
    }
    else if(strcmp("rank", fn) == 0){
        //to do the ranking based on a set of words, we should check the freqencies of each word, sum them and then rank the documents based on that
        char * words_data = NULL;
        int i;

        //for each document, return the ones which match the query
        mxml_node_t *node;
        //get parameters
        node = mxmlFindElement(tree, tree, "wordList", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            words_data = (char *)mxmlGetOpaque(node);
        }
        //sorted document list by rank
        int num_docs = 5;//num of docs
        rankedDoc *ranked = (rankedDoc *)malloc(sizeof(rankedDoc) * num_docs);
        int cur_doc = 0;

        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource\n");
            if(id == NULL) return err ("No id element found in resource\n");
             
            char *data = (char *)mxmlGetOpaque(content);
            //get the sum of the frequencies 
            double sum = get_sum(data, words_data);

            rankedDoc r;
            r.rank = sum;
            r.content = (char *)mxmlGetOpaque(content);
            r.id = (char *)mxmlGetOpaque(id);

            int i = cur_doc;
            ranked[i] = r;
            while(i > 0 && ranked[i-1].rank < ranked[i].rank){
                //swap
                rankedDoc tmp = ranked[i-1];
                ranked[i-1] = ranked[i];
                ranked[i] = tmp;
                i--;
            }
            cur_doc++;
            if(cur_doc >= num_docs){
                //increase size of array
                num_docs *= 2;
                ranked = (rankedDoc *)realloc(ranked, sizeof(rankedDoc) * num_docs);
            }
        }
        //print out list
        for(i = 0; i < cur_doc; i++){
            printf("<resource>");
            printf("<rank>%f</rank>", ranked[i].rank);
            printf("<id>%s</id>\n<content>\n", ranked[i].id);
            printf("%s\n", ranked[i].content);
            printf("</content>\n");
            printf("</resource>\n");
        }
        free(ranked);
    }
    else if(strcmp("vector_rank", fn) == 0){
        /*
        So ordinarily, implementing a vector model would involve building an index with all of the unique terms and a linked list with all 
        of the documents which have those terms so finding the tf-idf weights is faster for repeated queries. However, since our implementation 
        will only involve a single query at a time and will not store indexes for all possibilities of documents I propose just making vectors out
        of the query terms
        */

        //get query
        //to do the ranking based on a set of words, we should check the freqencies of each word, sum them and then rank the documents based on that
        char * words_data = NULL;
        int i;
        int num_docs = 0;
        int ranked_docs_size = 10;//assume 10 docs
        rankedDoc * docs = (rankedDoc *)malloc(ranked_docs_size * sizeof(rankedDoc));

        //for each document, return the ones which match the query
        mxml_node_t *node;
        //get parameters
        node = mxmlFindElement(tree, tree, "wordList", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            words_data = (char *)mxmlGetOpaque(node);
        }
        //build that index
        vector_index * vi = makeIndex(words_data);//now we have a blank index with the terms

        //go through each document, add to index with frequency
        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource\n");
            if(id == NULL) return err ("No id element found in resource\n");
             
            char *data = (char *)mxmlGetOpaque(content);
            int id_num;
            char * id_str = (char *)mxmlGetOpaque(id);

            sscanf(id_str, "%d", &id_num);
            addToIndex(vi, data, id_num);

            docs[num_docs].id_num = id_num;
            docs[num_docs].id = id_str;
            docs[num_docs].content = data;
            docs[num_docs].rank = 0;

            num_docs++;
            if(num_docs >= ranked_docs_size){
                ranked_docs_size *= 2;
                docs = (rankedDoc *)realloc(docs, ranked_docs_size * sizeof(rankedDoc));
            }
        }

        //make vector of query and idf coefficients for each query term
        double * query_vector = (double *)malloc(vi->num_terms * sizeof(double));
        for(i = 0; i < vi->num_terms; i++){
            //calculate the idf for each term
            double n = (double)vi->lls[i]->num_docs;
            if(n == 0){
                query_vector[i] = 0;
            }
            else{
                query_vector[i] = log((double)num_docs / n);
            }
            //printf("Query Vector[%d] = %f\n", i, query_vector[i]);
        }

        /*
        TODO
         -also the linear searching might need to go, rethink how to go about finding the counts per term
         -also, I will need to sort this later and print in XML
         -there are serious memory leaks, ~50%
        */

        //for each document, make vector out of frequency of word *
        for(i = 0; i < num_docs; i++){
            //make vector
            double * doc_vector = (double *)malloc(vi->num_terms * sizeof(double));
            int j;
            for(j = 0; j < vi->num_terms; j++){
                int count = getCount(vi->lls[j], docs[i].id_num);
                doc_vector[j] = ((double)count) * query_vector[j];
            }

            double similarity;
            //dot product the vectors
            double num = dotProduct(doc_vector, query_vector, vi->num_terms);
            double denom = mag(doc_vector, vi->num_terms) * mag(query_vector, vi->num_terms);

            if(denom == 0){
                //one is a zero vector, so it's rank is 0 anyway
                similarity = 0;
            }
            else{
                similarity = num / denom;
            }
            docs[i].rank = similarity;
            j = i;
            while(j > 0 && docs[j].rank > docs[j-1].rank){
                rankedDoc tmp = docs[j-1];
                docs[j-1] = docs[j];
                docs[j] = tmp;
                j--;
            }
            free(doc_vector);
        }

        for(i = 0; i < num_docs; i++){
            printf("<resource>");
            printf("<id>%d</id>\n", docs[i].id_num);
            printf("<rank>%f</rank>\n", docs[i].rank);
            printf("<content>%s</content>", docs[i].content);
            printf("</resource>");
        }

        free(query_vector);
        free(docs);
        freeIndex(vi, 1);
    }
    else if(strcmp("extract_blocks", fn) == 0){
        int searchWindow = 0;
        int resultWindow = 0;
        int useStemming = 0;
        char * words_data = NULL;
        int i;
        
        //for each document, return the ones which match the query
        mxml_node_t *node;
        //get parameters
        node = mxmlFindElement(tree, tree, "wordList", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            words_data = (char *)mxmlGetOpaque(node);
        }

        node = mxmlFindElement(tree, tree, "searchWindow", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &searchWindow);
        }
        else{
            return err("searchWindow required\n");
        }

        node = mxmlFindElement(tree, tree, "resultWindow", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            sscanf(mxmlGetOpaque(node), "%d", &resultWindow);
        }
        else{
            return err("resultWindow required\n");
        }

        node = mxmlFindElement(tree, tree, "useStemming", NULL, NULL, MXML_DESCEND);
        if(node != NULL){
            char * us = (char *)mxmlGetOpaque(node);
            if(strcmp(us, "TRUE") == 0 || strcmp(us, "true") == 0){
                useStemming = 1;
            }
        }

        //make a prefix tree out of the words
        int index = 0;
        char * word = getWord(words_data, &index);
        pnode words_tree = makePTree();
        int num_words = 0;
        while(word != NULL){
            addToPrefixWithId(&words_tree, word, num_words);
            word = getWord(words_data, &index);
            num_words++;
        }

        for(node = mxmlFindElement(tree, tree, "resource", NULL, NULL, MXML_DESCEND); node != NULL; node = mxmlFindElement(node, tree, "resource", NULL, NULL, MXML_DESCEND)){
            mxml_node_t *content = mxmlFindElement(node, node, "content", NULL, NULL, MXML_DESCEND);
            mxml_node_t *id = mxmlFindElement(node, node, "id", NULL, NULL, MXML_DESCEND);

            if(content == NULL) return err("No content element found in resource\n");
            if(id == NULL) return err ("No id element found in resource\n");
             
            char *data = (char *)mxmlGetOpaque(content);
            //search data for blocks
            printf("<resource><id>%s</id>", mxmlGetOpaque(id));
            int num_matches = 0;
            char ** blocks = getBlocks(data, &words_tree, num_words, searchWindow, resultWindow, useStemming, &num_matches);
            if(num_matches > 0){
                printf("\n<blockList>");
            }
            for(i = 0; i < num_matches; i++){
                printf("\n<block>\n%s\n</block>\n", blocks[i]);
            }
            if(num_matches > 0){
                printf("</blockList>\n");
            }
            printf("<content>%s</content>", mxmlGetOpaque(content));
            printf("</resource>\n");
        }
    }
    
    mxmlDelete(tree);
    return 0;
}
