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

	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $cxn;
		if(!pe($xml, "table")) die(err("Table element not found"));
		if(!pe($xml, "fields")) die(err("No fields found"));
		$table = (string)$xml->table;
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
		$idField = parent::getIdField($table);
		if(!$idField){
			die(err("Table " . $table . " does not exist"));
		}

		foreach($xml->resourceList->resource as $res){
			if($first){
				$first = false;
			}
			else{
				$statement .= " OR ";
			}
			$id;
			if(pe($res, "id")){
				$id = $res->id;
			}
			else{
				die(err("Resource does not have required id field"));
			}

			$statement .= "`".esc($idField)."`='".esc($id)."'";
		}
		
		mysqli_query($cxn, $statement) or die(err("Could not update database with query: " . $statement));
		$response = "<parameters><requestID>" . $REQ_ID ."</requestID><requestType>update</requestType>";
		$response .= $resXML . "</parameters>";
		return $response;
	}
}