<?php
/*
###Request
```
<parameters>
	<requestType>pipe</requestType>
	<commandList>
		<command>
			(Any of the input formats for the commands)
		</command>
		<command>
			(This command will get the resourceList input from the previous command, therefore it is unnecessary to include a resourceList in this command.)
		</command>
		...
	</commandList>
</parameters>
```
###Response
The response will follow the format of the last executed command.
###Pipe Examples
####Example 1
This example calls select on snippets with ids from 10 to 20 and then deletes the results

Notice that the delete command is missing the resourceList since it will be automatically filled by the output of the select statement.
```
<parameters>
	<requestType>pipe</requestType>
	<commandList>
		<command>
			<parameters>
				<requestType>select</requestType>
				<table>snippets</table>
				<where>
						<logic type="and">
							<field operator="gte">
								<name>snippetID</name>
								<value>10</value>
							</field>
							<field operator="lte">
								<name>snippetID</name>
								<value>20</value>
							</field>
						</logic>
				</where>
			</parameters>
		</command>
		<command>
			<parameters>
				<requestType>delete</requestType>
			</parameters>
		</command>
	</commandList>
</parameters>
*/
class Pipe extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $cxn, $VALID_REQUEST_TYPES;

		$resLists = array();//array of resLists to manage
		$cmdList = array(); //history of commands
		$requiresInput = Array();//array of this format: controllerName => if it needs input
		foreach($VALID_REQUEST_TYPES as $val){
			$requiresInput[$val] = true;
		}
		//exceptions
		$requiresInput["select"] = false;
		$requiresInput["insert"] = false;

		if(!pe($xml, "command")) die(err("No commands found"));

		$i = 0;
		foreach($xml->command as $cmd){
			$rt = (string)$cmd->parameters->requestType;
			$cmdList[$i] = $rt;
			$cmdObj = $cmd->parameters;
			if($requiresInput[$rt]){
				if($i == 0 || empty($resLists)){
					//check if input already supplied
					if(!pe($cmd->parameters, "resourceList") || !pe($cmd->parameters, "resourceLists")){
						die(err($rt . " required input which is not given"));
					}
				}
				else{

					//add any previous resourceLists as input
					$reqStr = (string)$cmd->parameters->asXML();
					/*
					a little hacky, but cheaper than converting to another system. If I have a lot of extra time, 
					I may consider changing from SimpleXML to something more flexible
					*/
					$nextXMLStr = substr($reqStr, 0, strlen($reqStr) - strlen("</parameters>"));

					foreach($resLists as $resList){
						$nextXMLStr .= $resList->asXML();
					}
					$nextXMLStr = $nextXMLStr . "</parameters>";

					try{
						$cmdObj = new SimpleXMLElement($nextXMLStr);
					}
					catch(Exception $e){
						die(err("Could not pipe requests"));
					}
					$resLists = array();
				}
				
			}
			$output = new SimpleXMLElement(handleRequest($cmdObj));
			if(pe($output, "resourceList")){
				foreach($output->resourceList as $rl){
					array_push($resLists, $rl);
				}
			}
			$i++;
		}
		echo $output->asXML();
	}
}