CC=g++
FLAGS=-g

summarize.out : main.o tinystr.o tinyxml.o tinyxmlerror.o tinyxmlparser.o
	$(CC) -o summarize.out main.o tinystr.o tinyxml.o tinyxmlerror.o tinyxmlparser.o

main.o: main.cpp
	$(CC) -c main.cpp
tinystr.o: tinystr.cpp
	$(CC) -c tinystr.cpp
tinyxml.o: tinyxml.cpp
	$(CC) -c tinyxml.cpp
tinyxmlerror.o: tinyxmlerror.cpp
	$(CC) -c tinyxmlerror.cpp
tinyxmlparser.o: tinyxmlparser.cpp
	$(CC) -c tinyxmlparser.cpp
clean:
	rm -rf main.o tinystr.o tinyxml.o tinyxmlerror.o tinyxmlparser.o summarize.out

