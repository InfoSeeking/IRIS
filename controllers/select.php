<?php
/*
Example select request:
<parameters>
	<requestType>select</requestType>
	<fields>
		<field>
			field name
		</field>
		<field>
			field name
		</field>
		...
	</fields>
	<table>
		table name (pages|annotation|snippet|bookmarks|searches)
	</table>
	<where>
		(<logic type="and|or|not")
			<field operator="eq|ne|lt|gt|lte|gte|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			<field operator="eq|ne|lt|gt|lte|gte|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			...
		(</logic>)
		...
	</where>
	(<orderby type="desc|asc">
		<field>
			field name
		</field>
	 </orderby>)
	 (<limit>
	 	number
	 (</limit)
</parameters>
*/

class Select extends Controller{
	private function parseLogicField($field){
		//get attribute
		$op = cleanOp(parent::getAttr($field, "operator"));
		if(!$op){
			die(err("Missing operator attribute on field tag"));
		}
		return sprintf("`%s`%s%s", esc($field->name) , $op , esc($field->value));
	}
	/* initially called with the where node (which can only have one node) */
	private function parseLogic($root, $connective){
		$logicString = "";
		$si; //xmliterator to go through the children of the $root
		if(!($root instanceOf SimpleXMLIterator) && ($root instanceOf SimpleXMLElement)){
			try{
				$si = new SimpleXMLIterator($root->asXML());
			}
			catch(Exception $e){
				die(err($root->asXML()));
			}
		}
		else if($root instanceOf SimpleXMLIterator){
			$si = $root;
		}
		else{
			return false;
		}
		if(!$connective){
			$connective = "";
		}
		$first = true;
		$logicString .= "(";
		for($si->rewind(); $si->valid(); $si->next()) {
			if($first){
				$first = false;
			}
			else{
				if($connective == "not"){
					die(err("Logical not can not tie statments together"));
				}
				$logicString .= " " . $connective . " ";
			}

			if($si->key() == "logic"){
				$c = cleanLogic(parent::getAttr($si->current(), "type"));
				if(!$c){
					die(err("Missing/Invalid type attribute on logic tag, must be and/or/not"));
				}
				if($c == "not"){
					//not wraps around
					$logicString .= $c . " ";
				}
				$logicString .= $this->parseLogic($si->current(), $c);
			}
			else if($si->key() == "field"){
				//easy
				$logicString .= $this->parseLogicField($si->current());
			}
		}
		$logicString .= ")";
		return $logicString;
	}

	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $cxn;
		$fields = "*";
		$table = false;
		$additional = "";

		//check if there are fields
		if(pe($xml, "fields") && pe($xml->fields, "field")){
			$fields = "";
			$first = true;
			foreach($xml->fields->field as $field){
				if($first)
					$first = false;
				else
					$fields .= ",";
				$fields .= "`" . esc($field) . "`";
			}
		}

		if(pe($xml, "table") && table_valid($xml->table)){
			$table = esc($xml->table);
		}
		else{
			die(err("No table in query"));
		}

		if(pe($xml, "where")){
				$additional .= " WHERE " . $this->parseLogic($xml->where, false);
		}

		if(pe($xml, "orderby")){
			if(!pe($xml->orderby, "field")){
				die(err("Orderby element missing field element"));
			}
			$type = esc(strtolower(trim(parent::getAttr($xml->orderby, "type"))));
			if(!$type){
				$type = "asc";
			}
			$field = "`" . esc($xml->orderby->field) . "`";
			$additional .= " ORDER BY " . $field . " " .$type;
		}

		if(pe($xml, "limit")){
			$additional .= " LIMIT " . esc($xml->limit);
		}

		$statement = "SELECT " . $fields . " FROM " . $table . $additional;
		$response = "<parameters><table>" . $table . "</table><requestID>" . $REQ_ID . "</requestID><requestType>select</requestType><resourceList>";
		$results = mysqli_query($cxn, $statement) or die(err("Could not run query: " . $statement));
		while($row = $results->fetch_assoc()){
			$response .= "<resource><type>" . $table . "</type><fields>";
			foreach($row as $key => $val)
				$response .= sprintf("<field><name>%s</name><value>%s</value></field>", $key, $val);
			$response  .= "</fields></resource>";
		}
		$response .= "	</resourceList></parameters>";
		$results->free();
		return $response;
	}
}