#ifndef PHP_HELLO_H
#define PHP_HELLO_H 1
#define PHP_HELLO_WORLD_VERSION "1.0"
#define PHP_HELLO_WORLD_EXTNAME "text_processing"

PHP_FUNCTION(extract_keywords);

extern zend_module_entry text_processing_module_entry;
#define phpext_hello_ptr &text_processing_module_entry

typedef struct{
	char *word;
	int count;
	double freq;
} keyword;

typedef struct node_s{
	keyword *data;
	struct node_s *next;
} node;

typedef struct{
	node **buckets;
	int size;
} hashtable;


#endif
