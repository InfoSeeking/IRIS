<?php
$API_ENDPOINT = "http://iris.comminfo.rutgers.edu";
//$API_ENDPOINT = "http://localhost/IRIS/application/index.php";
//using this proxy, the IP address is from the web server, not the client's computer

if(!isset($_POST['xmldata'])) die("Nothing passed");

$data = array('xmldata' => $_POST['xmldata']);

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ),
);

$context  = stream_context_create($options);
$result = file_get_contents($API_ENDPOINT, false, $context);

echo $result;

?>