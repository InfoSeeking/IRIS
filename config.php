<?php
/* configurable 'constants' you may edit these to reflect your system/development stage */

$ROOT="http://localhost/CoagmentoMiddleLayer/application/";
$STORAGE="storage/"; //storage folder (writable directory)
$LIB="library/";
$CONTROLLER="controllers/";
$FILE_ROOT="";
$STATE="live"; //valid values are debug,live
$HOST=""; //valid values are local,live

if($_SERVER["HTTP_HOST"] == "localhost"){
	$HOST="local";
}
else{
	$HOST="live";
}

/* do not edit below this */
$cmd_extra = " 2>&1";
if($STATE=="live"){
	$cmd_extra = " 1>/dev/null 2>&1";
}

$REQ_ID = "";//to be generated

?>