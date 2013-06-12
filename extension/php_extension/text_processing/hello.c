#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_hello.h"


/*
Current issues:
- I am unable to use the PHP extension emalloc functions, so I am using regular malloc
- When I try freeing the buckets, I get segfaults, but I cannot debug. I will try to compile PHP for debugging later if time allows
*/
static zend_function_entry text_processing_functions[] = {
    PHP_FE(extract_keywords, NULL)
    {NULL, NULL, NULL}
};

zend_module_entry text_processing_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
    STANDARD_MODULE_HEADER,
#endif
    PHP_HELLO_WORLD_EXTNAME,
    text_processing_functions,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
#if ZEND_MODULE_API_NO >= 20010901
    PHP_HELLO_WORLD_VERSION,
#endif
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_HELLO
ZEND_GET_MODULE(text_processing)
#endif


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
        return 1;
    }

}

void rehash(hashtable *table, int size){
    table->size = size;
    if(table->buckets == 0){
        table->buckets = (node **)malloc(size * sizeof(node*));
    }
    else{
        //todo
        //table->keywords = (keyword *)realloc(table->keywords, size * sizeof(keyword));  
    }
}

keyword ** findFrequencies(hashtable *table, double totalWords, int uniqueWords){
    keyword **sorted = (keyword **)malloc(sizeof(keyword *) * uniqueWords);

    int count = 0;
    int i;
    for(i = 0; i < table->size; i++){
        node *n;
        for(n = table->buckets[i]; n != 0; n = n->next){
            n->data->freq = ((double)n->data->count) / totalWords;
           

            int j = count;
            sorted[j] = n->data;
            //insertion sort O(n^2), may replace with better algorithm later
            while(j > 0 && sorted[j]->freq > sorted[j-1]->freq){
                keyword *tmp = sorted[j-1];
                sorted[j-1] = sorted[j];
                sorted[j] = tmp;
                j--;
            }
            count++;
        }
    }



    return sorted;
}


//recursively frees a linked list
void freeLL(node *n){
    if(n->next == 0){
        free(n);
    }
    else{
        freeLL(n->next);
        free(n);
    }
}

void freeHash(hashtable *table){
    //todo
    int i;
    for(i = 0; i < table->size; i++){
        //freeLL(table->buckets[i]); - Not working, gives segfaults, can't debug
    }

    free(table);
}

PHP_FUNCTION(extract_keywords){
    char * data;
    int data_len;
    long num_entries;
    if(zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sl", &data, &data_len, &num_entries) == FAILURE){
        RETURN_NULL();
    }

    
    hashtable *table = (hashtable *)malloc(sizeof(hashtable));
    
    rehash(table, 100);

    //go through data, get word by word, add to keywords hash table
    int wordIndex = 0;
    int origWordSize = 10;
    int wordSize = origWordSize;
    int i;
    int j;
    double totalWords = 0;
    int uniqueWords = 0;

    for(i = 0; i < data_len; i++){
        wordIndex = 0;
        wordSize = origWordSize;
        char *word = (char *)malloc(sizeof(char) * wordSize);
        for(j = i; j <= data_len; j++){
            char c;
            short end = 0;
            if(j == data_len){
                //end of text, still need null character
                if(wordIndex == 0){
                    //no end word, probably ended with a .
                    continue;
                }
                c = '\0';//null character
                end = 1;
            }
            else{
                c = data[j];
            }

            if(c == ' ' || c == '\n' || c == ',' || c == '.'){
                if(wordIndex == 0){
                    //there is no beginning, keep waiting
                    continue;
                }
                //end of word
                c = '\0';//null character
                end = 1;
            }
            
            if(wordIndex >= wordSize){
                wordSize *= 2;
                word = (char *)realloc(word, sizeof(char) * wordSize);
            }
            
            word[wordIndex] = c;
            wordIndex++;

            if(end){
                break;
            }
        }
        i=j;
        if(wordIndex > 0){
            totalWords++;
            //make lowercase
            int k;
            for(k = 0; k < strlen(word); k++){
                word[k] = tolower(word[k]);
            }
            uniqueWords += addToHash(table, word);
        }
    }

    keyword ** sorted = findFrequencies(table, totalWords, uniqueWords);
    zval * ret_arr;
    //return_value is automatically return value
    array_init(return_value);
    //add to return array the number of words the user requested
    long upperBound = num_entries < (long)uniqueWords ? num_entries : uniqueWords;
    for(i = 0; i < upperBound; i++){
        //php_printf("WORD: %s\tFREQ: %f\n", sorted[i]->word, sorted[i]->freq);
        add_assoc_double(return_value, sorted[i]->word, (double)sorted[i]->freq);
    }
    freeHash(table);
    return;
}