<?php
class Index_insert extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $cxn, $LIB;
		if(!pe($xml, "resourceList")) die(err("resourceList element not found"));

		
		$pers = strtolower($xml->persistence) == "true" ? TRUE : FALSE;
		

		$TREC = fopen(fname($STORAGE . "trec.txt"), "w");
		foreach($xml->resourceList->resource as $res){
				fwrite($TREC, "<DOC>\n<DOCNO>" . $res->id ."</DOCNO>\n<TEXT>\n");
				//fwrite($TREC, "<title>" . $title . "</title>");
				fwrite($TREC, $res->content);
				fwrite($TREC, "</TEXT>\n</DOC>\n");
		}
		fclose($TREC);

		//make parameter file
		//now build the index
		$IPARAM = fopen(fname($STORAGE . "build_index.param"), "w");

		$index_id = $REQ_ID;
		//check if user is adding onto an index
		if(pe($xml, "indexID")){
			//try to get it
			$index_id = intval((string)$xml->indexID);
		}

		//get stopwords
		$stopwords = file_get_contents($LIB . "stopwords_indri.param");
		
		$index_dir = $STORAGE ."indexes/index_" . $index_id;
		$tmp = "<parameters><index>" . $index_dir . "</index><corpus><path>" . fname($STORAGE . "trec.txt") . "</path><class>trectext</class></corpus><stopper>" . $stopwords . "</stopper></parameters>";
		fwrite($IPARAM, $tmp);
		fclose($IPARAM);

		//build index with that parameter file, if user supplied indexID add to the existing index
		//now build the index
		system($BIN . "IndriBuildIndex " . fname($STORAGE . "build_index.param") . $CMD_EXTRA);
		$response = "<parameters><requestID>" . $REQ_ID ."</requestID><indexID>" . $index_id . "</indexID><requestType>index_insert</requestType></parameters>";
		return $response;
		
	}
}