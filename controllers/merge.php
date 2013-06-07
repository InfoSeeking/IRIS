<?php
/*
<parameters>
	<requestType>merge</requestType>
	<resourceLists>
		<resourceList>
			<resource>
			
			</resource>
			<resource>
				
			</resource>
			...
		</resourceList>
		<resourceList>
			<resource>
				
			</resource>
			<resource>
				
			</resource>
			...
		</resourceList>
		...
	</resourceLists>
</parameters>
*/

class Merge extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $cxn;
		$response = "<parameters><requestID>" . $REQ_ID . "</requestID><requestType>merge</requestType><resourceList>";
		if(pe($xml, "resourceLists")){
			foreach($xml->resourceLists->resourceList as $list){
				foreach($list->resource as $res){
					$response .= $res->asXML();	
				}
			}
		}
		else{
			die(err("No resourceList elements found"));
		}
		$response .= "</resourceList></parameters>";
		return $response;
	}
}