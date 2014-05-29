<?php
class Controller{
	/* run takes the xml object and returns the output as a string */
	function run($xml){
		return "Default Controller";
	}
	private $tableToId = array(
		"pages" => "pageID",
		"snippets" => "snippetID",
		"annotations" => "noteID",
		//"snippets" => "snippetID" 
		);
	public function getIdField($table){
		if(array_key_exists($table, $this->tableToId)){
			return $this->tableToId[$table];
		}
		else{
			return false;
		}
	}
	protected function getAttr($xml, $at){
		foreach($xml->attributes() as $name => $val){
			if($name == $at){
				return $val;
			}
		}
		return false;
	}
	function getFieldVal($fields, $fieldName){
		foreach($fields->field as $field){
			if($field->name == $fieldName){
				return $field->value;
			}
		}
		return false;
	}
	//takes the string operators (eq,gt,lt, ...) and does the test
	function opTest($op, $val1, $val2){
		switch($op){
			case "eq":
			return $val1 == $val2;
			case "ne":
			return $val1 != $val2;
			case "gt":
			return $val1 > $val2;
			case "gte":
			return $val1 >= $val2;
			case "lt":
			return $val1 < $val2;
			case "lte":
			return $val1 <= $val2;
		}
	}

}
