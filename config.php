<?php
/* configurable 'constants' you may edit these to reflect your system/development stage */

$ROOT="http://localhost/CoagmentoMiddleLayer/application/";
$STORAGE="storage/"; //storage folder (writable directory)
$LIB="library/";
$BIN="bin/";
$CONTROLLER="controllers/";
$FILE_ROOT="";
$STATE="debug"; //valid values are debug,live
$HOST=""; //valid values are local,live
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
	"halt"
	);

if($_SERVER["HTTP_HOST"] == "localhost"){
	$HOST="local";
}
else{
	$HOST="live";
}

/* do not edit below this */
$CMD_EXTRA = " 2>&1";
if($STATE=="live"){
	$CMD_EXTRA = " 1>/dev/null 2>&1";
}

$REQ_ID = "";//to be generated
$PERSISTENCE = FALSE;//whether or not to delete files after request is over
?>