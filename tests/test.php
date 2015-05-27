<?php
/*
This file is used to test if your local IRIS setup is functioning.
Run it via command line like so:
php -f test.php -- <input_file>

Change API_ENDPOINT to match yours if your setup differs.
*/
$API_ENDPOINT = "http://localhost/IRIS/index.php";
//using this proxy, the IP address is from the web server, not the client's computer

if(count($argv) > 1){
  $data = file_get_contents($argv[1]);
  if(!$data){
    die("Could not read file " . $argv[1]);
  }
} else {
  echo "Usage: php -f test.php -- <input_file>\n";
  echo "Example: php -f test.php -- examples/sample_filter.xml";
  exit();
}

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

echo "==========BEGIN IRIS RESPONSE==========\n";
echo $result;
echo "===========END IRIS RESPONSE===========\n";
?>
