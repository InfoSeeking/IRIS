COMPILER=gcc
CCFLAGS=-g 
all: text_processing

text_processing: text_processing.o extract.o hashtable.o filter.o 
	$(COMPILER) -o text_processing text_processing.o extract.o hashtable.o filter.o -lmxml -pthread

text_processing.o: text_processing.c text_processing.h extract.h hashtable.h
	$(COMPILER) $(CCFLAGS) -c text_processing.c text_processing.h extract.h hashtable.h

hashtable.o: hashtable.c hashtable.h extract.h
	$(COMPILER) $(CCFLAGS) -c hashtable.c hashtable.h extract.h

extract.o: extract.c extract.h hashtable.h
	$(COMPILER) $(CCFLAGS) -c extract.c extract.h hashtable.h

filter.o: filter.c filter.h hashtable.h
	$(COMPILER) $(CCFLAGS) -c filter.c filter.h hashtable.h

clean:
	rm -rf text_processing.o extract.o hashtable.o filter.o text_processing *.gch
