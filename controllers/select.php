<?php
/*
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
		(<and>|<or>|<not>)
			<field operator="=|>|<|>=|<=|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			<field operator="=|>|<|>=|<=|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			...
		(</and>|</or>|</not>)
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
$response = "in progress";

class Select extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID;
		$fields = "";
		//check if there are fields
		if(pe($xml, "fields") && pe($xml->$fields, "field")){
			$first = true;
			foreach($xml->fields->field as $field){
				if($first)
					$first = false;
				else
					$fields .= ",";
				$fields .= "`" . esc($field) . "`";
			}
		}

		return $fields;
	}
}