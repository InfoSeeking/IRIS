<?php
require_once("../../config.php");
if(isset($_GET['sample'])){
	//ok good
	$uSample = $_GET['sample'];
	if(in_array($uSample, $VALID_REQUEST_TYPES)){
		//good to go
		exit(file_get_contents("../examples/sample_" . $uSample . ".xml"));
	}
}