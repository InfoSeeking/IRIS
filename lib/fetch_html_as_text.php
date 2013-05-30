<?php
//given the 
include("htmlparser.php");
$txtname = 0;
$url = "";
$outputDir = "";

if($argc == 4){
	$txtname = $argv[1];
	$url = $argv[2];
	$outputDir = $argv[3];
}
else{
	echo $argc;
	var_dump($argv);
	die("Invalid parameters");
}
if($argc == 4){
	$outputDir = $argv[3];
}
$html = file_get_html($url);
foreach($html->find('title') as $element);
$f = fopen($outputDir . "txt/" . $txtname . ".txt", 'w');
fwrite($f, $element);
fwrite($f, $html->plaintext);
fclose($f);
?>