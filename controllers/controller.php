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
		"snippets" => "snippetID"
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
}