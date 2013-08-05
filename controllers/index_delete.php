<?php
class Index_delete extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $PERSISTENCE, $CMD_EXTRA;
		if(!pe($xml, "indexID")) die(err("IndexID element not found"));

		$index_id;
		//check if user is adding onto an index
		if(pe($xml, "indexID")){
			//try to get it
			$index_id = intval((string)$xml->indexID);
		}
		$index_dir = $STORAGE ."indexes/index_" . $xml->clientID . "_" . $index_id;
		system("rm -rf " . $index_dir . $CMD_EXTRA);

		$response = "<parameters><requestID>" . $REQ_ID ."</requestID><indexID>" . $index_id . " </indexID><requestType>index_delete</requestType></parameters>";
		return $response;
		
	}
}