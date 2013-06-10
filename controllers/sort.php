<?php
/*
<parameters>
	<requestType>sort</requestType>
	<table>table name</table>
	<sortField>(defaults to the id)</sortField>
	<orderby type="desc|asc">
		field name
	</orderby>
	<resourceList>
		<resource>
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
	<table>table name</table>
	<requestType>sort</requestType>
	<resourceList>
		<resource>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
*/

class Sort extends Controller{
	private $sortField = "id";
	private $flip = 1;


	private function resSort($a, $b){
		//todo add sorting for other fields
		if($this->sortField == "id"){
			return (intval($a->id) - intval($b->id)) * $this->flip;
		}
		else{
			//get the field
			if(pe($a, "fields") || pe($b, "fields")){
				$af = parent::getFieldVal($a->fields, $this->sortField);
				$bf = parent::getFieldVal($b->fields, $this->sortField);
				if($af === false || $bf === false){
					die(err("Sort field not in resource"));
				}
				if(is_numeric($af)){
					return (intval($af) - intval($bf)) * $this->flip;
				}
				else{
					return strcmp($af, $bf) * $this->flip;
				}
			}
			else{
				die(err("Sort fields not in resource"));
			}
		}
	}
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID;
		if(!pe($xml, "table")) die(err("No table found"));
		if(!pe($xml, "resourceList")) die(err("No resources found"));

		$table = (string)$xml->table;

		if(pe($xml, "orderby")){
			$this->sortField = (string)$xml->orderby;
			if($order = parent::getAttr($xml->orderby, "type")){
				if($order == "desc"){
					$this->flip = -1;
				}
			}
		}
		
		$resArr = array();
		$resOut = "";
		foreach($xml->resourceList->resource as $res){
			array_push($resArr, $res);
		}
		if(!usort($resArr, array($this, "resSort"))){
			die(err("Couldn't sort"));
		}

		foreach($resArr as $val){
			$resOut .= $val->asXML();
		}
		$response = "<parameters><requestType>sort</requestType><requestID>". $REQ_ID . "</requestID><resourceList>". $resOut . "</resourceList></parameters>";
		return $response;
	}
}