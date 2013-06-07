<?php
/*
<parameters>
	<requestType>insert</requestType>
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
</parameters>
*/

class Insert extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $cxn;
		if(!pe($xml, "table")) die(err("Table element not found"));
		if(!pe($xml, "fields")) die(err("No fields found"));
		$table = $xml->table;
		$statement = sprintf("INSERT INTO %s (", esc($table));
		$names = "";
		$values = "";
		$first = true;
		foreach($xml->fields->field as $field){
			if($first){
				$first = false;
			}
			else{
				$names .= ",";
				$values .= ",";
			}
			$names .= "`".esc($field->name)."`";
			$values .= "'".esc($field->value)."'";
		}
		$statement .= $names . ") VALUES(" . $values . ")";
		mysqli_query($cxn, $statement) or die(err("Could not insert into database with query: " . $statement));
		$id = mysqli_insert_id($cxn);
		$response = "<parameters><requestID>" . $REQ_ID ."</requestID><insertID>". $id ."</insertID><requestType>insert</requestType></parameters>";
		return $response;
		
	}
}