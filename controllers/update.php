<?php
/*
##Update
###Request
```
<parameters>
	<requestType>update</requestType>
	<table>table name</table>
	<fields>
		<field>
			<name>
				field name
			</name>
			<value>
				field value
			</value>
		</field>
		...
	</fields>
	<resourceList>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>update</requestType>
	<resourceList>
		...
	</resourceList>
</parameters>
```
*/

class Update extends Controller{
	private function getAttr($xml, $at){
		foreach($xml->attributes() as $name => $val){
			if($name == $at){
				return $val;
			}
		}
		return false;
	}
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $cxn;
		if(!pe($xml, "table")) die(err("Table element not found"));
		if(!pe($xml, "fields")) die(err("No fields found"));
		$table = $xml->table;
		$statement = sprintf("UPDATE %s SET", esc($table));

		$first = true;
		foreach($xml->fields->field as $field){
			if($first){
				$first = false;
			}
			else{
				$statement .= ",";
			}
			$statement .= "`".esc($field->name)."`=";
			$statement .= "'".esc($field->value)."'";
		}
		$statement .= " WHERE ";
		$first = true;
		$resXML = $xml->resourceList->asXML();
		/* find primary of each resource */
		foreach($xml->resourceList->resource as $res){
			if($first){
				$first = false;
			}
			else{
				$statement .= " OR ";
			}
			$primary = false;
			foreach($res->fields->field as $field){
				if($this->getAttr($field, "type") == "primary"){
					$primary = $field;
					break;
				}
			}
			$statement .= "`".esc($primary->name)."`='".esc($primary->value)."'";
		}
		
		mysqli_query($cxn, $statement) or die(err("Could not update database with query: " . $statement));
		$response = "<parameters><requestID>" . $REQ_ID ."</requestID><requestType>update</requestType>";
		$response .= $resXML . "</parameters>";
		return $response;
	}
}