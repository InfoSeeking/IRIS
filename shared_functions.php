<?php
require_once("htmlparser.php");
function err($msg){
	return "<error><message>$msg</message></error>";
}

function fetch_to_trec($url, $doc_id, $TREC){
	global $FILE_ROOT;
	$html = file_get_html($url);
	foreach($html->find('title') as $element);
	fwrite($TREC, "<DOC>\n<DOCNO>" . $doc_id ."</DOCNO>\n<TEXT>\n");
	fwrite($TREC, $element);
	fwrite($TREC, $html->plaintext);
	fwrite($TREC, "</TEXT>\n</DOC>\n");

	$TXT = fopen($FILE_ROOT . "output/txt/" . $doc_id . ".txt", "w");
	fwrite($TXT, $element . $html->plaintext);
	fclose($TXT);
}

function clean(){
	system("rm -r output/clusterIndex output/summarizationIndex");
}
?>