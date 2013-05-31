# Copyright 2009 Chirag Shah
# Script to read a list of text files and combine them in TREC format.
# Usage: ./combineDocs.pl < <input_file>
#!/usr/bin/perl

$dir = $ARGV[1];#working directory
open(FILELIST, $ARGV[0]) || die "Error reading the input file.\n";
open(OUTFILE, ">" . $dir . "documents.txt") || die "Error creating the output file.\n";

$docNo = 1;
while ($docName = <FILELIST>)
{
	print OUTFILE "<DOC>\n<DOCNO>$docNo</DOCNO>\n<TEXT>\n";
	open (INFILE, $dir.$docName);
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
