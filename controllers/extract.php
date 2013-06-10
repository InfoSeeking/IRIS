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

		$resOut = "";
		$table = (string)$xml->table;
		$amt = intval($xml->amount);
		$off = 0;
		$i = 0;
		if(pe($xml, "offset"))
			$off = intval($xml->offset);

		foreach($xml->resourceList->resource as $res){
			if($off > 0){
				$off--;
				continue;//skip
			}
			if($i >= $amt)
				break;
			$resOut .= $res->asXML();
			$i++;
		}
		$response = "<parameters><requestType>limit</requestType><requestID>". $REQ_ID . "</requestID><resourceList>". $resOut . "</resourceList></parameters>";
		return $response;
	}
}