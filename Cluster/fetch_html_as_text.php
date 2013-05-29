<?php
//given the 
include("htmlparser.php");
$txtname = 0;
$url = "";

if($argc == 3){
	$txtname = $argv[1];
	$url = $argv[2];
}
else{
	echo $argc;
	var_dump($argv);
	die("Invalid parameters");
}
$html = file_get_html($url);
foreach($html->find('title') as $element);
$f = fopen("./txt/" . $txtname . ".txt", 'w');
fwrite($f, $element);
fwrite($f, $html->plaintext);
fclose($f);
?>