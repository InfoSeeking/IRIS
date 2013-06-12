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
		global $FILE_ROOT, $STORAGE, $REQ_ID;
		if(!pe($xml, "table")) die(err("No table found"));
		if(!pe($xml, "resourceList")) die(err("No resources found"));

		$num_words = 10;
		if(pe($xml, "numWords")){
			$num_words = intval($xml->numWords);
		}

		foreach($xml->resourceList->resource as $res){
			$url_arr = getUrlArray(array($res->id));
			$url = $url_arr["" . $res->id];
			$plaintext = getPlainText(file_get_contents($url));
			echo $plaintext;
			//$res->addChild("keywords", implode(extract_keywords($plaintext, $num_words)));
		}

		$response = "<parameters><requestType>limit</requestType><requestID>". $REQ_ID . "</requestID>" . $xml->resourceList->asXML() . "</parameters>";
		return $response;
	}
}