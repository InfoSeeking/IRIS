<?php
/*
requests should be made with the request type on the end of the url like so:
<api url>/<request type>
and the data should be passed through POST as the xmldata parameter
*/

function err($msg){
	echo "<error><message>$msg</message></error>";
}

$reqType = substr($_SERVER["PATH_INFO"], 1);
$validTypes = array("cluster", "summarize");
$valid = false;
for($i = 0; $i < sizeof($validTypes); $i++){
	if($validTypes[$i] == $reqType){
		$valid = true;
	}
}

switch($reqType){
	case "cluster":
	case "summarize":
		//ok
	break;
	default:
		err("Request type not valid, possible types are: ".implode($validTypes, ", "));
		die();
	break;
}

//get xml data and write to a file
if(isset($_POST['xmldata'])){
	$in = fopen("input/input.xml", 'w');
	//TODO, check if valid xml data (or maybe in C++ prog)
	fwrite($in, $_POST['xmldata']);
	fclose($in);
}
else{
	err("No xml data provided");
	die();
}

chdir($reqType);
$ret = 0;
system("./". $reqType .".out -i ../input/input.xml > /dev/null", $ret);//use exec and check stdout
if($ret == 1){
	err("Error in processing");
	die();
}
echo file_get_contents("output.xml");

?>
