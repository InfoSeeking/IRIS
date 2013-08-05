<?php
/*
<parameters>
	<requestType>index_query</requestType>
	<indexID>number</indexID>
	<query>
		(indri type query) documentation [here](http://sourceforge.net/p/lemur/wiki/IndriRunQuery/)
	</query>
</parameters>

<parameters>
	<requestType>index_query</requestType>
	<requestID>number</requestID>
	<indexID>number</indexID>
	<resourceList>
		<resource>
		 	<score>indri query score (sorted in descending order)</score>
		 	<url>page url</url>
		 </resource>
		...
	</resourceList>
</parameters>
*/
class Index_query extends Controller{

	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $PERSISTENCE, $CMD_EXTRA, $cxn;
		if(!pe($xml, "indexID")) die(err("IndexID element not found"));
		if(!pe($xml, "query")) die(err("Query element not found"));

		
		//write to parameter file
		$fn = fname($STORAGE . "query.param");
		$HANDLE = fopen($fn, "w");
		$queryStr = "<parameters>";
		foreach($xml->indexID as $indexID){
			$index_dir = $STORAGE . "indexes/index_" . $xml->clientID . "_" . (string)$indexID;
			if(!file_exists($index_dir)){
				die(err("Index " . $indexID . " does not exist"));
			}
			$queryStr .= "<index>" . $index_dir ."</index>";
		}
		

		$queryStr .= $xml->query->asXML();

		$queryStr .= "</parameters>";
		
		fwrite($HANDLE, $queryStr);
		fclose($HANDLE);

		$out = Array();
		exec($BIN. "IndriRunQuery " . $fn . " ", $out);
		$response = "<parameters><requestType>index_query</requestType><requestID>". $REQ_ID ."</requestID>";
		$response .= "<resourceList>";
		$scores = Array();
		foreach($out as $line){
			$parts = preg_split("/\s+/", $line);
			$scores[$parts[1]] = $parts[0]; //id => score
		}
		foreach($scores as $id => $score){
			$response .= "<resource><score>" . $score . "</score><id>". $id . "</id></resource>";
		}
		$response .= "</resourceList></parameters>";
		return $response;
	}
}