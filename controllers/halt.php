<?php
class Halt extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		exit("<parameters><requestType>halt</requestType><clientID>".$xml->clientID . "</clientID><requestID>" . $REQ_ID . "</requestID></parameters>");
	}
}