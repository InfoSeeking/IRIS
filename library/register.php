<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>IRIS</title>
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" type="image/icon" href="library/inc/favicon.ico" />
  <style type="text/css">
	.container{
		width: 500px;
		margin: 10px auto;
	}
	h1{
		font-family: 'Arial', 'sans-serif';
	}
	label{
		display: inline-block;
		width: 20%;
	}
	.faded{
		color: #AAA;
	}
	input{
		width: 78%;
		float: right;
	}
	.row{
		clear: right;
	}
	input[type=submit]{
		width: 150px;
		padding: 5px 10px;
		margin-left: auto;
		display: block;
		margin-top: 10px;
	}
	.err{
		background: #FFBFBF;
		padding: .5em 1em;
	}
	#logo{
		font-weight: normal;
		margin: 0px;
	}
	#logo img{
		vertical-align: middle;
	}
	#logo .shift{
		position: relative;
		top: 3px;
	}
  </style>

  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
<div class="container">
	<h1 id="logo"><img width="50" src="library/inc/logo_eye.png" /> <span class="shift">IRIS</span></h1>
	<h1>API Registration</h1>

<?php

$name = "";
$email = "";
$website = "";
$client_id = "";

function printErr($msg){
	global $name, $email, $website, $client_id;
	echo "<div class='msg err'>" . $msg . "</div>";
	include("inc/form.php");
}

function process(){
	global $name, $email, $website, $client_id, $cxn, $PUBLICLY_RESERVED;
	if(!isset($_POST["website"]) || !isset($_POST['name']) || !isset($_POST['email'])){
		include("inc/form.php");
		return;
	}
	
	//validate, add to db
	$name = trim($_POST['name']);
	$email = trim($_POST['email']);
	$website = trim($_POST['website']);

	if($email == ""){return printErr("Please enter your email.");}
	if($website == ""){return printErr("Please enter the website.");}
	if($name == ""){return printErr("Please enter your name.");}

	//remove the protocol off of the website
	if(strpos($website, "http://") === 0){
		$website = substr($website, strlen("http://"));
	}
	if(strpos($website, "https://") === 0){
		$website = substr($website, strlen("https://"));
	}
	//try getting the ip for the website
	$ip = gethostbyname($website);
	if($ip == $website){
		//failed
		return printErr("Could not fetch IP address for <b>" . $website . "</b>, please double check that this address is correct.");
	}
	if(!preg_match("/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i", $email)){
		return printErr("Email address <b>". $email . "</b> seems to be incorrect.");
	}
	if($name == ""){
		return printErr("Please enter in your name.");
	}
	$website = esc($website);
	$email = esc($email);
	$name = esc($name);
	//check if this website already exists or if this email already exists
	$query = sprintf("SELECT * FROM clients WHERE `website`='%s'", $website);
	$r = mysqli_query($cxn, $query) or die("Could not query database");
	if(mysqli_num_rows($r) > 0){
		return printErr(sprintf("There is already a registration for the website <b>%s</b>", $website));
	}
	$query = sprintf("SELECT * FROM clients WHERE `email`='%s'", $email);
	$r = mysqli_query($cxn, $query) or die("Could not query database");
	if(mysqli_num_rows($r) > 0){
		return printErr(sprintf("There is already a registration with the email <b>%s</b>", $email));
	}

	//good to go, add to database
	$query = sprintf("INSERT INTO clients (`website`, `name`, `email`) VALUES('%s', '%s', '%s')", $website, $name, $email);
	mysqli_query($cxn, $query) or die("Could not add registration");
	//get client id
	$client_id = mysqli_insert_id($cxn) + $PUBLICLY_RESERVED;
	include("inc/success.php");
}

process();

?>
	
	<p>More information on IRIS can be found on our online <a href="https://github.com/kevinAlbs/IRIS" target="_blank">GitHub documentation</a>.
</div>
</body>
</html>