<?php
//fetches document contents from url's
//TODO add persistence/page_caching
class Fetch extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $PERSISTENCE, $CMD_EXTRA;
		if(!pe($xml, "resourceList")) die(err("resourceList element not found"));
		$resp = "<parameters><requestID>" . $REQ_ID . "</requestID><requestType>fetch</requestType><resourceList>";
		foreach($xml->resourceList->resource as $res){
			//no page cache checking yet since that still uses the pages table
			$html = @file_get_contents($res->url);
			$plainText = "";
			if($html){
				$plainText = getPlainText($html);
			}
			$resp .= "<url>".$res->url."</url><content>" . $plainText . "</content>";
		}
		$resp .= "</resourceList></parameters>";
		return $resp;
	}
}