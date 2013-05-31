<?php
require_once("config.php");
require_once("shared_functions.php");
/*
requests should be made with the request type on the end of the url like so:
<api url>/<request type>
and the data should be passed through POST as the xmldata parameter
*/


$xmldata;//raw xml data
$xml;//simplexml object


//get xml data and write to a file
if(!isset($_REQUEST['xmldata'])){//TODO CHANGE TO POST once live
	die(err("No xml data provided"));
}
$xmldata = $_REQUEST['xmldata'];
$xml = new SimpleXMLElement($xmldata);
$type = $xml->requestType;

$validTypes = array("cluster", "summarize");
$valid = false;
for($i = 0; $i < sizeof($validTypes); $i++){
	if($validTypes[$i] == $type){
		$valid = true;
	}
}
require_once("controllers/" . $type . ".php");


?>
