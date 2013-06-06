<?php
class Summarize extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID;
		
		$ids = Array();
		$urls = Array();//associative id => url
		$numOfSentences = intval($xml->maxSentences);
		$numOfDocuments = 0;

		$TREC = fopen(fname($STORAGE . "trec.txt"), "w");//TODO make sure that you're not overwriting anything with a unique id or something

		$TREC_FILE_LIST = fopen($FILE_ROOT . fname($STORAGE . "trec_file.list"), "w");
		fwrite($TREC_FILE_LIST, $FILE_ROOT . fname($STORAGE . "trec.txt"));
		fclose($TREC_FILE_LIST);

		foreach($xml->docList->doc as $doc){
			array_push($ids, $doc->docID);
			$numOfDocuments++;
		}

		$urls = getUrlArray($ids);

		foreach($urls as $id => $url){
			fetch_to_trec($url, $id, $TREC);
		}
		fclose($TREC);

		//now build the index
		$IPARAM = fopen(fname($STORAGE . "build_index.param"), "w");
		$tmp = "<parameters><index>" . fname($STORAGE . "index") . "</index><indexType>indri</indexType><dataFiles>" . fname($STORAGE . "trec_file.list") . "</dataFiles><docFormat>trec</docFormat><stopwords>" . $LIB . "stopwords.param</stopwords></parameters>";
		fwrite($IPARAM, $tmp);
		fclose($IPARAM);

		//now build the index
		system($FILE_ROOT . "bin/BuildIndex " . fname($STORAGE . "build_index.param") . $cmd_extra);

		//now do summarization
		$response .= "<parameters>\n";
		$response .= "<requestID>" . $REQ_ID . "</requestID>";
		$response .= "<requestType>summarize</requestType>\n";
		$response .= "<docList>\n";

		foreach($urls as $id => $url){
			//create summarization parameters for each document
			$CPARAM = fopen(fname($STORAGE . "sum.param"), "w");
			fwrite($CPARAM, "<parameters>\n<index>" . fname($STORAGE . "index") . "</index><summLength>" . $numOfSentences . "</summLength><docID>" . $id . "</docID></parameters>");
			fclose($CPARAM);
			$arr = Array();
			//cannot pass parameter file by absolute path to command line! must use relative path
			exec($FILE_ROOT . "bin/BasicSummApp " . fname($STORAGE . "sum.param"), $arr);
			$response .= "<doc><docID>". $id ."</docID>";
			$response .= "<summarization>" . implode($arr, "\n") . "</summarization>";
			$response .= "</doc>";
		}
		$response .= "</docList></parameters>";
	}
}
