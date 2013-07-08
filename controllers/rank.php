<?php
/*
<parameters>
	<requestType>rank</requestType>
	<wordlist>list of words to check</wordlist>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
*/

class Rank extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		//if(!pe($xml, "table")) die(err("No table found"));
		if(!pe($xml, "resourceList")) die(err("No resources found"));

		//write to temporary file
		$fname = $STORAGE . $REQ_ID . "_rank.xml";
		$TMP = fopen($fname, "w");
		fwrite($TMP, $xml->asXML());
		fclose($TMP);
		//extract those words
		$cmd = $BIN . "text_processing rank " . $fname;
		$output = array();
		exec($cmd, $output);
		unlink($fname);
		$extractedResList = implode($output);
		$response = "<parameters><requestType>rank</requestType><requestID>". $REQ_ID . "</requestID><resourceList>" . $extractedResList . "</resourceList></parameters>";
		return $response;
	}
}