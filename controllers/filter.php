<?php
/*
<parameters>
	<requestType>filter</requestType>
	<stopWords>words</stopWords>
	<minLength>number</minLength>
	<maxLength>number</maxLength>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
*/

class Filter extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		if(!pe($xml, "table")) die(err("No table found"));
		if(!pe($xml, "resourceList")) die(err("No resources found"));
		//write to temporary file
		$fname = $STORAGE . $REQ_ID . "_filter.xml";
		$TMP = fopen($fname, "w");
		fwrite($TMP, $xml->asXML());
		fclose($TMP);
		//extract those words
		$cmd = $BIN . "text_processing filter " . $fname;
		$output = array();
		exec($cmd, $output);
		unlink($fname);
		$extractedResList = implode($output);
		$response = "<parameters><requestType>filter</requestType><requestID>". $REQ_ID . "</requestID><resourceList>" . $extractedResList . "</resourceList></parameters>";
		return $response;
	}
}