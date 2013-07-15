<?php
/*
<parameters>
	<requestType>if</requestType>
	<if>
		<statement>
			<path fxn="length|exists">xpath selector</path>
			<index>nth node (optional)</index>
			<test>eq|ne|lt|lte|gt|gte (optional)</test>
			<val>value (optional)</val>
		</statement>
		<command>
			...
		</command>
	</if>
	<elif>
		(optional)
		<statement>
			...
		</statement>
		<command>
			...
		</command>
	</elif>
	<else>
		(optional)
		<command>
			...
		</command>
	</else>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
*/

class If_then extends Controller{
	//returns value of if statement
	private function parseStatment($smt, $xml){
		return false;
	}
	//set up and call pipe
	private function finalize($nextCmd, $xml){

	}
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		if(!pe($xml, "resourceList")) die(err("No resources found"));
		if(!pe($xml, "if")) die(err("No if statement found"));

		if($this->parseStatement($xml->if->statement, $xml)){
			//move the command and call the pipe controller
			return $this->finalize($xml->if->command, $xml)
		}
		
		//check for elif's
		if(pe($xml, "elif")){
			foreach($xml->elif as $elif){
				if($this->parseStatement($elif->statement, $xml)){
					return $this->finalize($elif->command, $xml);
				}
			}
		}
		
		//check for else
		if(pe($xml, "else")){	
			if($this->parseStatement($xml->else->statement, $xml)){
				return $this->finalize($xml->else->command, $xml);
			}	
		}

		//otherwise return something that says nothing happened...
		return $xml->asXML();//TODO
	}
}