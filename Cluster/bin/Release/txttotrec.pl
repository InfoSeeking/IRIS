# Copyright 2009 Chirag Shah
# Script to read a list of text files and combine them in TREC format.
# Usage: ./combineDocs.pl < <input_file>
#!/usr/bin/perl

open(FILELIST, $ARGV[0]) || die "Error reading the input file.\n";
open(OUTFILE, ">documents.txt") || die "Error creating the output file.\n";

$docNo = 1;
while ($docName = <FILELIST>)
{
	print OUTFILE "<DOC>\n<DOCNO>$docNo</DOCNO>\n<TEXT>\n";
	open (INFILE, $docName);
	while ($line = <INFILE>) 
	{
		print OUTFILE $line;
	}
	close(INFILE);
	print OUTFILE "</TEXT>\n</DOC>\n";
	$docNo++;
}

close(FILELIST);
close(OUTFILE);
