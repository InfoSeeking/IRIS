<?php

//eventually make this an associative array
$urls = Array();
$numOfSentences = intval($xml->maxSentences);
$numOfDocuments = 0;
$i = 1;//dummy id's
$TREC = fopen("output/trec.txt", "w");//TODO make sure that you're not overwriting anything with a unique id or something

$TREC_FILE_LIST = fopen($FILE_ROOT . "output/trec_file.list", "w");
fwrite($TREC_FILE_LIST, $FILE_ROOT . "output/trec.txt");
fclose($TREC_FILE_LIST);

foreach($xml->docList->doc as $doc){
	array_push($urls, $doc->url);
	fetch_to_trec($doc->url, $i, $TREC, $FLIST);
	$i++;
	$numOfDocuments++;
}


//now build the index
system($FILE_ROOT . "bin/BuildIndex output/buildSummarizationIndex.param" . $cmd_extra);

//now do summarization
$response .= "<parameters>\n";
$response .= "<requestID>" . $REQ_ID . "</requestID>";
$response .= "<requestType>summarize</requestType>\n";
$response .= "<docList>\n";

for($i = 0; $i < $numOfDocuments; $i++){
	//create summarization parameters for each document
	$CPARAM = fopen("output/sum.param", "w");
	fwrite($CPARAM, "<parameters>\n<index>output/summarizationIndex</index><summLength>" . $numOfSentences . "</summLength><docID>" . ($i + 1). "</docID></parameters>");
	fclose($CPARAM);
	$arr = Array();
	//cannot pass parameter file by absolute path to command line! must use relative path
	exec($FILE_ROOT . "bin/BasicSummApp output/sum.param", $arr);
	$response .= "<doc><docID>". $i ."</docID>";
	$response .= "<summarization>" . implode($arr, "\n") . "</summarization>";
	$response .= "</doc>";
}

echo $response;
