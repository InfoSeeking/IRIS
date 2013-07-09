<?php
/*
<parameters>
	<requestType>summarize_sentences</requestType>
	<numSentences>number</numSentences>
	<wordList></wordList>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>summarize_sentences</requestType>
	<resourceList>
		<resource>
			<id>id</id>
			<content type='summarized'></content>
		</resource>
		...
	</resourceList>
</parameters>
*/

class Summarize_sentences extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		// /if(!pe($xml, "table")) die(err("No table found"));
		if(!pe($xml, "resourceList")) die(err("No resources found"));
		
		//write to temporary file
		$fname = $STORAGE . $REQ_ID . "_summarize_sentences.xml";
		$TMP = fopen($fname, "w");
		fwrite($TMP, $xml->asXML());
		fclose($TMP);
		//extract those words
		$cmd = $BIN . "text_processing summarize " . $fname;
		$output = array();
		exec($cmd, $output);
		unlink($fname);
		$extractedResList = implode($output);
		$response = "<parameters><requestType>summarize_sentences</requestType><requestID>". $REQ_ID . "</requestID><resourceList>" . $extractedResList . "</resourceList></parameters>";
		return $response;
	}
}