#ifndef TEXT_PROCESSING_H
#define TEXT_PROCESSING_H
#include "extract.h"
#include <stdio.h>
int err(char * message);
void printHelp();
char * getWord(FILE *handle);
#endif