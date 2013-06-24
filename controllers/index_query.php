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
	//maybe take out if needed in future controllers
	function idsToUrls($ids){
		global $cxn;

		foreach($ids as $i => $id){
			$ids[$i] = intval($id);//escape
		}
		$query = "SELECT * FROM docs WHERE `doc_id` IN (" . implode(",", $ids) . ")";
		$r = mysqli_query($cxn, $query) or die(err("Could not select urls from db"));
		$assocArr = Array();
		while($row = mysqli_fetch_assoc($r)){
			$assocArr[$row['doc_id']] = $row['url'];
		}
		return $assocArr;
	}

	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $PERSISTENCE, $CMD_EXTRA, $cxn;
		if(!pe($xml, "indexID")) die(err("IndexID element not found"));
		if(!pe($xml, "query")) die(err("Query element not found"));

		
		
		
		//write to parameter file
		$fn = fname($STORAGE . "query.param");
		$HANDLE = fopen($fn, "w");
		$queryStr = "<parameters>";
		foreach($xml->indexID as $indexID){
			$index_dir = $STORAGE . "indexes/index_" . (string)$indexID;
			$queryStr .= "<index>" . $index_dir ."</index>";
		}
		

		$queryStr .= $xml->query->asXML();

		$queryStr .= "</parameters>";
		
		fwrite($HANDLE, $queryStr);
		fclose($HANDLE);

		$out = Array();
		exec($BIN. "IndriRunQuery " . $fn . " " . $CMD_EXTRA, $out);

		$response = "<parameters><requestType>index_query</requestType><requestID>". $REQ_ID ."</requestID>";
		$response .= "<resourceList>";
		$scores = Array();
		foreach($out as $line){
			$parts = preg_split("/\s+/", $line);
			$scores[$parts[1]] = $parts[0]; //id => score
		}
		$idToUrl = $this->idsToUrls(array_keys($scores));//use id's to get a mapping of ids to urls
		foreach($scores as $id => $score){
			$response .= "<resource><score>" . $score . "</score><url>". $idToUrl[$id] . "</url></resource>";
		}

		$response .= "</resourceList></parameters>";
		return $response;
	}
}