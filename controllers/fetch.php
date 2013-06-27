<?php
//fetches document contents from url's
//TODO add persistence/page_caching
class Fetch extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $PERSISTENCE, $CMD_EXTRA;
		if(!pe($xml, "resourceList")) die(err("resourceList element not found"));
		$resp = "<parameters><requestID>" . $REQ_ID . "</requestID><requestType>fetch</requestType>";
		$resp .= $xml->resourceList->asXML();//does it automatically lol
		$resp .= "</parameters>";
		return $resp;
	}
}