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
		return $statement;
		//This is where I left off on Friday, June 7th 2013 at 3:56PM
		//This is where I shall begin
		//...on Monday...
		
		/*
		mysqli_query($cxn, $statement) or die(err("Could not insert into database with query: " . $statement));
		$id = mysqli_insert_id($cxn);
		$response = "<parameters><requestID>" . $REQ_ID ."</requestID><insertID>". $id ."</insertID><requestType>insert</requestType></parameters>";
		return $response;
		*/
	}
}