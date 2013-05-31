<?php
require_once("htmlparser.php");
function err($msg){
	return "<parameters><error><message>$msg</message></error></parameters>";
}

/* gets a unique absolute filename using the REQ_ID */
function fname($fname){
	global $REQ_ID, $FILE_ROOT;
	return $FILE_ROOT . $fname . "." . $REQ_ID;
}
//returns doc_title
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
	return $element;
}

function clean(){
	system("rm -r output/clusterIndex output/summarizationIndex output/cluster.param output/sum.param output/trec_file.list output/trec.txt txt/*");
}
?>