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
if(!pe($xml, 'clientID')){
	$xml->addChild("clientID", "1");
}
//validate client id
$clientID = intval($xml->clientID);
//if <= 10000, then this is a public id, no worries
if($clientID > $PUBLICLY_RESERVED){
	//must check that this matches
	$ip = $_SERVER['REMOTE_ADDR'];
	//get the associated website for this client id
	//remember, in the database, the registered ids start at 0 (so subtract 10,000)
	$db_id = $clientID - $PUBLICLY_RESERVED;
	$r = mysqli_query($cxn, "SELECT * FROM clients WHERE `client_id`=" . $db_id) or die(err("Could not query database for client/website association"));
	if(mysqli_num_rows($r) == 0){
		die(err("There is no registration for a client id of '" . $clientID . "'. Client ids over " . $PUBLICLY_RESERVED . " are for registration only. If you wish to use publicly available client ids, use an id less than or equal to" . $PUBLICLY_RESERVED));
	}
	else{
		$row = mysqli_fetch_assoc($r);
		$website = $row['website'];
		$web_ip = gethostbyname($website);
		if($ip != $web_ip){
			if($STATE=='debug'){
				die(err("Our records show that the client id of '" . $clientID . "' is registered. However, the IP address from which you are calling (" . $ip . ") does not match the IP address of the website. (" . $web_ip . ")"));
			}
			else{
				die(err("Our records show that the client id of '" . $clientID . "' is registered. However, the IP address from which you are calling does not match the IP address of the website."));	
			}
		}
		//good to go
	}
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
