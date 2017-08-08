<?php
/* configurable 'constants' you may edit these to reflect your system/development stage */

$ROOT="http://localhost/CoagmentoMiddleLayer/application/";//index.php should be here
$STORAGE="storage/"; //storage folder (writable directory)
$LIB="library/";
$BIN="bin/";
$CONTROLLER="controllers/";
$FILE_ROOT="";
$STATE="live"; //valid values are debug,live
$HOST=""; //valid values are local,live
$CACHING=false;
$STORE_REQUESTS = true; //if this is true then each request made will be stored in storage/requests
$STORE_RESPONSES = true;
$PUBLICLY_RESERVED = 10000; //number of client ids that can be used for the public
$arr = array();
/*
In database:
+ client id starts at 1 and goes up
+ client id < 1 (in operator_requests) means that it is a public request
*/
$VALID_REQUEST_TYPES = array(
	"merge",
	"limit",
	"sort",
	"pipe",
	"extract",
	"filter",
	"query",
	"rank",
	"vector_rank",
	"index_insert",
	"index_delete",
	"index_query",
	"fetch",
	"extract_blocks",
	"summarize_sentences",
	"if_then",
	"halt",
	"cluster",
	"example",
	"example2",
	"example_java",
	"helloworld",
	"categorize",
	"get_category",
	"move_file",
	"return_files"
	);

if($_SERVER["HTTP_HOST"] == "localhost"){
	$HOST="local";
	//$STATE="debug";
}
else if($_SERVER["HTTP_HOST"] == "iris.comminfo.rutgers.edu"){
	$HOST="live";
	$STATE = "live";
	$BIN = "";//LD_LIBRARY_PATH=/usr/local/lib bin/";
}

/* do not edit below this... or else */
$CMD_EXTRA = " 2>&1";
if($STATE=="live"){
	$CMD_EXTRA = " 1>/dev/null 2>&1";
}

$REQ_ID = "";//to be generated
$PERSISTENCE = FALSE;//whether or not to delete files after request is over
?>
