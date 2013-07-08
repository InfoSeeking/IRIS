<?php
/*
<parameters>
	<requestType>query</requestType>
	<wordlist>list of words to check</wordlist>
	<query>
		<type>eq|ne|lt|gt</type>
		<value>value</value>
	</query>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
*/

class Query extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		if(!pe($xml, "resourceList")) die(err("No resources found"));

		//write to temporary file
		$fname = $STORAGE . $REQ_ID . "_query.xml";
		$TMP = fopen($fname, "w");
		fwrite($TMP, $xml->asXML());
		fclose($TMP);
		//extract those words
		$cmd = $BIN . "text_processing query " . $fname;
		$output = array();
		exec($cmd, $output);
		unlink($fname);
		$extractedResList = implode($output);
		$response = "<parameters><requestType>query</requestType><requestID>". $REQ_ID . "</requestID><resourceList>" . $extractedResList . "</resourceList></parameters>";
		return $response;
	}
}