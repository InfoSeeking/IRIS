#Usage
./cluster.out -i <input file>
-i <input file>		XML Document containing the document url's to cluster, as well as the number of clusters. See sample.xml for an example.

As of now, you MUST run this program from the same directory.

#Compiling
Compiling can be done with just 'make', if you need to add more files, be sure to edit the Makefile

#Files and Directories
sample.xml - example input
txt/ - folder for retrieved documents
buildindex.param - parameters for indexing
clusterIndex - index of documents retrieved in txt folder
