<?php

class Delete extends Controller{

	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $cxn;
		if(!pe($xml, "table")) { 
			die(err("Table element not found")); 
			}
		

		$table = (string)$xml->table;
		$statement = sprintf("DELETE FROM %s WHERE ", esc($table));

		/* find primary of each resource */
		$idField = parent::getIdField($table);
		if(!$idField){
			die(err("Table " . $table . " does not exist"));
		}
		$first = true;
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

		mysqli_query($cxn, $statement) or die(err("Could not delete from database with query: " . $statement));
		if(mysqli_affected_rows($cxn) == 0){
			die(err("Nothing was deleted"));
		}
		$response = "<parameters><table>" . $table . "</table><requestID>" . $REQ_ID ."</requestID><requestType>delete</requestType>";
		$response .= $resXML . "</parameters>";
		return $response;
		
	}
}
