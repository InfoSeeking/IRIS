<?php
/*
<parameters>
	<requestType>extract</requestType>
	<numWords>number</numWords>
	<resourceList>
		<resource>
			<table>table name</table>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>extract</requestType>
	<resourceList>
		<resource>
			<id>id</id>
			<keywords>comma,seperated,keywords</keywords>
		</resource>
		...
	</resourceList>
</parameters>
*/

class Extract extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		// /if(!pe($xml, "table")) die(err("No table found"));
		if(!pe($xml, "resourceList")) die(err("No resources found"));
		
		//write to temporary file
		$fname = $STORAGE . $REQ_ID . "_extract.xml";
		$TMP = fopen($fname, "w");
		fwrite($TMP, $xml->asXML());
		fclose($TMP);
		//extract those words
		$cmd = $BIN . "text_processing extract " . $fname;
		$output = array();
		exec($cmd, $output);
		unlink($fname);
		$extractedResList = implode($output);
		$response = "<parameters><requestType>extract</requestType><requestID>". $REQ_ID . "</requestID><resourceList>" . $extractedResList . "</resourceList></parameters>";
		return $response;
	}
}