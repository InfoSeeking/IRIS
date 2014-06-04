<?php
class Example extends Controller{
	function run($xml){
		global $REQ_ID;
		return "<parameters><requestType>filter</requestType><requestID>". $REQ_ID . "</requestID><resourceList></resourceList></parameters>";
	}
}