COMPILER=gcc
CCFLAGS=-g
all: text_processing
#at the moment, this requires lmxml to be installed, I will try later to make this portable
text_processing: text_processing.o extract.o hashtable.o filter.o query.o rank.o prefixtree.o vector_rank.o search_blocks.o summarize.o
	$(COMPILER) -o text_processing text_processing.o extract.o hashtable.o filter.o query.o rank.o prefixtree.o vector_rank.o search_blocks.o summarize.o -lmxml -pthread -lm

text_processing.o: text_processing.c
	$(COMPILER) $(CCFLAGS) -c text_processing.c

hashtable.o: hashtable.c
	$(COMPILER) $(CCFLAGS) -c hashtable.c

extract.o: extract.c
	$(COMPILER) $(CCFLAGS) -c extract.c

filter.o: filter.c
	$(COMPILER) $(CCFLAGS) -c filter.c

query.o: query.c
	$(COMPILER) $(CCFLAGS) -c query.c 

rank.o: rank.c
	$(COMPILER) $(CCFLAGS) -c rank.c

prefixtree.o: prefixtree.c
	$(COMPILER) $(CCFLAGS) -c prefixtree.c

vector_rank.o: vector_rank.c
	$(COMPILER) $(CCFLAGS) -c vector_rank.c

search_blocks.o: search_blocks.c
	$(COMPILER) $(CCFLAGS) -c search_blocks.c

summarize.o: summarize.c
	$(COMPILER) $(CCFLAGS) -c summarize.c

clean:
	rm -rf text_processing.o extract.o hashtable.o filter.o query.o text_processing *.gch rank.o prefixtree.o vector_rank.o search_blocks.o summarize.o
