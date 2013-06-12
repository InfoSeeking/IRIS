
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