<?php
require_once("magic_quotes.php");
require_once("config.php");
require_once("dbconfig.php");
require_once("shared_functions.php");

/*
requests should be made with the request type on the end of the url like so:
<api url>/<request type>
and the data should be passed through POST as the xmldata parameter

request files are saved under output/requests
response files are saved under output/responses
The files are named with the request id

*/

$xmldata;//raw xml data
$xml;//simplexml object
$response = "";//controller should put response in this variable

//get xml data and write to a file
if(!isset($_REQUEST['xmldata'])){//change to POST when live
	die(err("No xml data provided"));
}

$xmldata = $_REQUEST['xmldata'];
$xml = false;
try{
	echo $xmldata;
	$xml = new SimpleXMLElement($xmldata);
}
catch(Exception $e){
	die(err("Xml could not be parsed"));
}
$type = $xml->requestType;

$validTypes = array("cluster", "summarize");
$valid = false;
for($i = 0; $i < sizeof($validTypes); $i++){
	if($validTypes[$i] == $type){
		$valid = true;
	}
}
if(!$valid){
	die(err("Request type not valid. Valid requests are: " . implode($validTypes, ", ")));
}

//add this request to the database
if(add_request($xml)){
	//it was found in cache!
	exit($response);
}

//save this request
$REQ_FILE = fopen("output/requests/" . $REQ_ID . ".xml", "w");
fwrite($REQ_FILE, $xmldata);
fclose($REQ_FILE);

//process
require_once("controllers/" . $type . ".php");

echo $response;

//save response
$RES_FILE = fopen("output/responses/" . $REQ_ID . ".xml", "w");
fwrite($RES_FILE, $response);
fclose($RES_FILE);

clean();
mysqli_close($cxn);
?>
