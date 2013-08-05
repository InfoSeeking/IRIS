<?php
require_once("config.php");
require_once("dbconfig.php");
require_once($CONTROLLER . "controller.php");
require_once($LIB . "magic_quotes.php");
require_once($LIB . "shared_functions.php");

header("Access-Control-Allow-Origin: *");

if(isset($_GET['pg'])){
	//load a page instead
	switch($_GET['pg']){
		case "register":
			die(include($LIB . $_GET['pg'] . ".php"));
		break;
	}
}
/*
requests should be made with the request type on the end of the url like so:
<api url>/<request type>
and the data should be passed through POST as the xmldata parameter

request files are saved under $STORAGE/requests
response files are saved under $STORAGE/responses
The files are named with the request id

*/

$xmldata;//raw xml data
$xml;//simplexml object
$response = "";

//get xml data and write to a file
if(!isset($_REQUEST['xmldata'])){//change to POST when live
	die(include($LIB . "noRequest.html"));
}

$xml = false;
$xmldata = $_REQUEST['xmldata'];
//die($xmldata);
try{
	$xml = new SimpleXMLElement($xmldata);
}
catch(Exception $e){
	die(err("XML could not be parsed"));
}
if(!$xml){
	die(err("XML could not be parsed"));
}
// will load the requestType controller
$response = handleRequest($xml);
echo $response;

if($STORE_REQUESTS){
	//save this request
	$fname = $STORAGE . "requests/" . $REQ_ID . ".xml";
	$REQ_FILE = fopen($fname, "w");
	fwrite($REQ_FILE, $xmldata);
	fclose($REQ_FILE);
}
if($STORE_RESPONSES){
	//save response
	$RES_FILE = fopen($STORAGE . "responses/" . $REQ_ID . ".xml", "w");
	fwrite($RES_FILE, $response);
	fclose($RES_FILE);
}
clean();
mysqli_close($cxn);
?>
