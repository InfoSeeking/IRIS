<?php
/* configurable 'constants' you may edit these to reflect your system/development stage */

$ROOT="http://localhost/CoagmentoMiddleLayer/application";

$FILE_ROOT="/home/kevin/public_html/CoagmentoMiddleLayer/application/";

$STATE="live";//valid values are live and local



/* do not edit below this */
$cmd_extra = " 2>&1";
if($STATE=="live"){
	$cmd_extra = " 1>/dev/null 2>&1";
}

?>