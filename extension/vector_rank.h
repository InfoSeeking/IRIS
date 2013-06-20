#ifndef VECTOR_RANK_H
#define VECTOR_RANK_H

typedef struct vi_node_t{
	struct vi_node_t * next;
	int doc_id;
	int term_count;//"frequency"
} vi_node;

typedef struct{
	vi_node *root;
	char * term;
	int num_docs;//number of documents in this linked list
} vi_ll;

typedef struct{
	vi_ll ** lls;//linked lists
	int num_terms;
} vector_index;

vector_index * makeIndex(char * query);
void addToIndex(vector_index *vi, char * doc_data, int doc_id);
void addToLinkedList(vi_ll * ll, int doc_id, int count);
int getCount(vi_ll *ll, int doc_id);
void freeIndex(vector_index *vi, int freeWords);
void freeIndexLL(vi_node *n);
//vector stuff
double dotProduct(double * v1, double * v2, int num_terms);
double mag(double * v1, int num_terms);
#endif