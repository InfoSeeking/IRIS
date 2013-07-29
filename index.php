<?php
require_once("config.php");
require_once("dbconfig.php");
require_once($CONTROLLER . "controller.php");
require_once($LIB . "magic_quotes.php");
require_once($LIB . "shared_functions.php");
require_once("autoload.php");

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
	die(err("No XML data provided. To pass XML data, send a POST request with a variable 'xmldata' set to your data. To see more information, view the <a href='http://iris.infoseeking.org'>website for IRIS</a>"));
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

use UnitedPrototype\GoogleAnalytics;
/* track the page */
if($USE_ANALYTICS){
	// Initilize GA Tracker
	//account_id and domain are declared in db_config
	$tracker = new GoogleAnalytics\Tracker($account_id, $domain);

	// Assemble Visitor information
	// (could also get unserialized from database)
	$visitor = new GoogleAnalytics\Visitor();
	$visitor->setIpAddress($_SERVER['REMOTE_ADDR']);
	$visitor->setUserAgent($_SERVER['HTTP_USER_AGENT']);

	// Assemble Session information
	// (could also get unserialized from PHP session)
	$session = new GoogleAnalytics\Session();

	// Assemble Page information
	$page = new GoogleAnalytics\Page('/index.php');
	$page->setTitle('API Endpoint');

	//track request type
	$reqType = new GoogleAnalytics\CustomVariable(1, "requestType", $xml->requestType, GoogleAnalytics\CustomVariable::SCOPE_PAGE);

	// Track page view
	$tracker->trackPageview($page, $session, $visitor);
}
?>
