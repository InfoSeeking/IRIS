#!/usr/bin/perl
@files = glob "*.txt";
$i = 1;
foreach $file (@files){
	open(HANDLE, $file);
	print "<resource><id>$i</id><content>";
	while(<HANDLE>){
		chomp;
		print "$_\n";
	}
	print "</content></resource>";
	$i++;
}