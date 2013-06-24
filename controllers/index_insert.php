<?php
class Index_insert extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $PERSISTENCE, $CMD_EXTRA, $cxn;
		if(!pe($xml, "resourceList")) die(err("resourceList element not found"));

		if(pe($xml, "persistence")){
			$PERSISTENCE = strtolower($xml->persistence) == "true" ? TRUE : FALSE;
		}

		$TREC_FILE_LIST = fopen($FILE_ROOT . fname($STORAGE . "trec_file.list"), "w");
		fwrite($TREC_FILE_LIST, $FILE_ROOT . fname($STORAGE . "trec.txt"));
		fclose($TREC_FILE_LIST);

		//insert documents into database, by using ignore, this will ignore failed inserts and thus not overwrite any existing urls
		$query = "INSERT IGNORE INTO docs (`url`) VALUES ";
		$selectQuery = "SELECT * FROM docs WHERE `url` IN (";
		$first = true;
		$num_urls = 0;
		foreach($xml->resourceList->resource as $res){
			$url = $res->url;
			if($first){
				$first = false;
			}
			else{
				$query .= ",";
				$selectQuery .= ",";
			}	
			$query .= "('" . esc($url) . "')";
			$selectQuery .= "'" . esc($url) . "'";
			$num_urls++;
		}
		$selectQuery .= ")";
		
		mysqli_query($cxn, $query) or die(err("Could not insert documents into database"));
		//now we have to select, the mysqli_insert_id will not give correct results if docs were ignored
		$results = mysqli_query($cxn, $selectQuery) or die(err("Could not select documents from database"));
		$TREC = fopen(fname($STORAGE . "trec.txt"), "w");
		while($row = mysqli_fetch_assoc($results)){
			fetch_to_trec($row['url'], $row['doc_id'], $TREC);	
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

		$index_dir = $STORAGE ."indexes/index_" . $index_id;
		$tmp = "<parameters><index>" . $index_dir . "</index><indexType>indri</indexType><dataFiles>" . fname($STORAGE . "trec_file.list") . "</dataFiles><docFormat>trec</docFormat><stopwords>" . $LIB . "stopwords.param</stopwords></parameters>";
		fwrite($IPARAM, $tmp);
		fclose($IPARAM);

		//build index with that parameter file, if user supplied indexID add to the existing index
		//now build the index
		system($FILE_ROOT . "bin/BuildIndex " . fname($STORAGE . "build_index.param") . $CMD_EXTRA);
		$response = "<parameters><requestID>" . $REQ_ID ."</requestID><indexID>" . $index_id . "</indexID><requestType>index_insert</requestType></parameters>";
		return $response;
		
	}
}