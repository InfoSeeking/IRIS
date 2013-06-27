<?php
/*
<parameters>
	<requestType>extract_blocks</requestType>
	<wordList>words</wordList>
	<searchWindow>20</searchWindow>
	<resultWindow>5</resultWindow> <!-- additional padding to search window -->
	<useStemming>true</useStemming>
	<resourceList>
		<resource>
			<id>id</id>
			<content>text</content>
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
			<blockList>
				<block>text</block>
				<block>text</block>
				...	
			</blockList>
			<content>...</content>
		</resource>
		...
	</resourceList>
</parameters>
*/

class Extract_blocks extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		if(!pe($xml, "resourceList")) die(err("No resources found"));
		
		//write to temporary file
		$fname = $STORAGE . $REQ_ID . "_extract_blocks.xml";
		$TMP = fopen($fname, "w");
		fwrite($TMP, $xml->asXML());
		fclose($TMP);
		//extract those words
		$cmd = $BIN . "text_processing extract_blocks " . $fname;
		$output = array();
		exec($cmd, $output);
		unlink($fname);
		$extractedResList = implode($output);
		$response = "<parameters><requestType>extract_blocks</requestType><requestID>". $REQ_ID . "</requestID><resourceList>" . $extractedResList . "</resourceList></parameters>";
		return $response;
	}
}