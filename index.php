<?php
require_once("config.php");
require_once("dbconfig.php");
require_once($CONTROLLER . "controller.php");
require_once($LIB . "magic_quotes.php");
require_once($LIB . "shared_functions.php");

header("Access-Control-Allow-Origin: *");
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
	die(err("No xml data provided"));
}

$xml = false;
$xmldata = $_REQUEST['xmldata'];
try{
	$xml = new SimpleXMLElement($xmldata);
}
catch(Exception $e){
	die(err("Xml could not be parsed"));
}
$response = handleRequest($xml);
echo $response;

//save this request
$REQ_FILE = fopen($STORAGE . "requests/" . $REQ_ID . ".xml", "w");
fwrite($REQ_FILE, $xmldata);
fclose($REQ_FILE);
//save response
$RES_FILE = fopen($STORAGE . "responses/" . $REQ_ID . ".xml", "w");
fwrite($RES_FILE, $response);
fclose($RES_FILE);

clean();
mysqli_close($cxn);
?>
