#ifndef PREFIXTREE_H
#define PREFIXTREE_H
typedef struct pnode_s{
	struct pnode_s *children; //array to be initialized if/when needed
	int prefixCount; //number of words which start with this sequence
	int count;//number of words which completely match this sequence
	int id;//for search blocks
} pnode;


pnode makePTree();
pnode * makeChildren();
int addToPrefix(pnode *tree, char * word);
int addToPrefixWithId(pnode *tree, char * word, int id);
pnode * fetchFromPrefix(pnode *tree, char * word, int useStemming); //fetches total match, returns NULL if no match
void freeTree(pnode *tree);
#endif