<?php
/*
<parameters>
	<requestType>if</requestType>
	<if>
		<statement>
			<val type="xpath|literal" nth="" fxn="length">value (optional)</val>
			<test>eq|ne|lt|lte|gt|gte|exists (optional)</test>
			<val type="xpath|literal" nth="" fxn="length">value (optional)</val>
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
	private function parseStatement($smt, $xml){
		$op = (string)$smt->test;
		//get both values (may only be 1 for exists etc.)
		$val1 = null;
		$val2 = null;
		foreach($smt->val as $v){
			$type = $this->getAttr($v, "type");
			$nth = $this->getAttr($v, "nth");
			$fxn = $this->getAttr($v, "fxn");
			$cleaned = "";
			$value = (string)$v;
			if($type && strtolower($type) == "xpath"){
				//get node
				$nodes = $xml->xpath($value); //this is an array
				if($nth && $nodes){
					if(sizeof($nodes) > $nth){
						$cleaned = (string)$nodes[$nth];
					}
				}
				else if($nodes && sizeof($nodes) > 0){
					$cleaned = (string)$nodes[0];
				}

				if($fxn && strtolower($fxn) == "length"){
					
					$cleaned = sizeof($nodes);
				}
			}
			else{
				//literal
				$cleaned = $value;
			}

			if($val1 == null){
				$val1 = $cleaned;
			}
			else{
				$val2 = $cleaned;
			}
		}
		//check op for special operators like exists
		if($op == "exists"){
			return $val1 != "";
		}
	//	printf("<h1>%s %s %s</h1>", $val1, $op, $val2);
		return $this->opTest($op, $val1, $val2);
	}
	/*
	I think the easiest way to run the final command is to do a pipe request of a fetch to the next command
	pipe:
		fetch => command
	*/
	private function finalize($nextCmd, $xml){
		$pipeCmd = "<parameters><requestType>pipe</requestType>";
		$sig = "";
		if(pe($xml, "clientID"))
			$sig .= $xml->clientID->asXML();
		if(pe($xml, "userID"))
			$sig .= $xml->userID->asXML();

		$pipeCmd .= $sig;
		$pipeCmd .= "<command><parameters><requestType>fetch</requestType>" . $sig . $xml->resourceList->asXML() . "</parameters></command>";
		$pipeCmd .= $nextCmd->asXML();
		$pipeCmd .= "</parameters>";
		return handleRequest(simplexml_load_string($pipeCmd));

	}

	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		if(!pe($xml, "resourceList")) die(err("No resources found"));
		if(!pe($xml, "if")) die(err("No if statement found"));

		if($this->parseStatement($xml->if->statement, $xml)){
			//move the command and call the pipe controller
			return $this->finalize($xml->if->command, $xml);
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
			return $this->finalize($xml->else->command, $xml);
		}

		//otherwise return something that says nothing happened...
		return "<parameters><requestType>if_then</requestType><requestID>". $REQ_ID . "</requestID><status>No branch taken</status></parameters>";
	}
}