<?php
function err($msg){
	return "<parameters><error><message>$msg</message></error></parameters>";
}

function esc($str){
	global $cxn;
	return mysqli_real_escape_string($cxn, $str);
}

/* shortened name */
function pe($class, $property){
	return property_exists($class, $property);
}

function cleanLogic($type){
	$type = trim(strtolower($type));
	switch($type){
		case "and":
		case "or":
		case "not":
		return $type;
		default: 
		return false;
	}
}

function cleanOp($op){
	$op = trim(strtolower($op));
	switch($op){
		case "like":
		case "in":
		return $op;
	}
	if ($op == "eq") return "=";
	if ($op == "ne") return "!=";
	if ($op == "lt") return "<";
	if ($op == "gt") return ">";
	if ($op == "lte") return "<=";
	if ($op == "gte") return ">=";
}
function handleRequest($xml){ 
	global $CONTROLLER, $VALID_REQUEST_TYPES;
	
	$type = $xml->requestType;
	$valid = false;
	for($i = 0; $i < sizeof($VALID_REQUEST_TYPES); $i++){
		if($VALID_REQUEST_TYPES[$i] == $type){
			$valid = true;
		}
	}
	if(!$valid){
		die(err("Request type not valid. Valid requests are: " . implode($VALID_REQUEST_TYPES, ", ")));
	}

	//add this request to the database
	if(add_request($xml)){
		//it was found in cache!
		exit($response);
	}

	//process
	require_once($CONTROLLER . $type . ".php");
	$cobj;//controller object
	try{
		$classname = ucfirst($type);
		$cobj = new $classname();
	}
	catch(Exception $e){
		die(err("Class . " . $classname . " does not exist"));
	}
	$response = $cobj->run($xml);
	return $response;
}

/*
the following function adds the request to the request table to keep a log
then it checks the request cache to see if this request has been made before
if it has it will return TRUE and set the $response variable to the cached output
*/
function add_request($xml){
	//called when request starts, adds request to database and generate's id
	global $REQ_ID, $cxn, $HOST, $response;
	//for now only test the request table locally
	
	$query = sprintf("INSERT INTO operator_requests (`reqType`, `date`, `time`) VALUES('%s', CURDATE(), CURTIME())", esc($xml->requestType));
	mysqli_query($cxn, $query) or die(err("Could not insert request into database"));
	$REQ_ID = mysqli_insert_id($cxn);
	return false;
/* cache stuff here
	if($HOST == "local"){
		// add to cache 
		$ids = Array();
		$docSum = 0;
		foreach($xml->docList->doc as $doc){
			array_push($ids, $doc->docID);
			$docSum += $doc->docID;
		}
		sort($ids,SORT_NUMERIC);
		$sig = implode($ids, ",");
		//check if there are entries in cache with same doc_sum and signature (doc_sum is for performance)
		$query = sprintf("SELECT `reqID`, `signature` FROM request_cache WHERE `reqType`='%s' AND `doc_sum`=%d AND `signature`='%s'", esc($xml->requestType), $docSum, $sig);
		$res = mysqli_query($cxn, $query) or die(err("Could not check cache for requests"));
		if(mysqli_num_rows($res) == 1){
			$row = mysqli_fetch_assoc($res);
			$fname = $STORAGE . "responses/" . $row["reqID"] . ".xml";
			//found
			if(file_exists($fname)){
				$HANDLE = fopen($fname, "r");
				$response = fread($HANDLE, filesize($fname));
				fclose($HANDLE);
				return true;//cache hit
			}
			else{
				return false;//even though in db, not on disk
			}
		}
		
		//insert into cache (preferably I would have this at the end in case of an error during, but it is convenient to place here for now)	
		$query = sprintf("INSERT INTO request_cache (`reqID`, `doc_sum`, `signature`, `reqType`) VALUES(%d, %d, '%s', '%s')", $REQ_ID, $docSum, $sig, esc($xml->requestType));
		mysqli_query($cxn, $query) or die(err("Could not add request to cache"));
		return false;
	}
	else{
		$REQ_ID = (string)time();
		return false;
	}
	*/
}

/* gets a relative filename using the REQ_ID */
function fname($fname){
	global $REQ_ID;
	if(!stristr($fname, ".")){
		//directory
		return $fname . "_" . $REQ_ID;
	}
	$parts = explode(".", $fname);
	return $parts[0] . "_" . $REQ_ID . "." . $parts[1];//fname_id.ext
}


/* these strip functions are for removing unnecessary html data */
function strip_tags_content($text, $tagStr) {
	$p1 = "@<" . $tagStr . ".*(>)@isUm";
	$p2 = "@</" . $tagStr . "(>)@iUsm";
	return slice_out($text, $p1, $p2);
}

function slice_out($text, $p1, $p2){
	$offset = 0;
	$c = 1;
	while(preg_match($p1, $text, $matches, PREG_OFFSET_CAPTURE)){
		$start = $matches[0][1];

		if(preg_match($p2, $text, $matches, PREG_OFFSET_CAPTURE, $start)){
			$end = $matches[1][1] + 1;
		}
		else{
			$end = $start;
		}
		unset($matches);
		$part1 = substr($text, 0, $start);
		$part2 = substr($text, $end);
		$text = $part1 . $part2;
		unset($part1);
		unset($part2);
	
	}
	return $text;
}

function strip_cdata($text){
	$start = TRUE;
	while($start !== FALSE){
		$start = strpos($text, "<![CDATA[");
		if($start === FALSE){
			break;
		}
		$end = strpos($text, "]]>", $start);
		if($end <= $start){
			break;
		}
		$part1 = substr($text, 0, $start);
		$part2 = substr($text, $end);
		$text = $part1 . $part2;
	}
	return $text;
}

function getPlainText($html){
	$start = 0;
	$end = 0;
	if(preg_match("@<body.*(>)@iUsm", $html, $matches, PREG_OFFSET_CAPTURE)){
		$start = $matches[1][1] + 1;
	}
	else{
		return false;
	}
	unset($matches);
	if(preg_match("@</body(>)@iUsm", $html, $matches, PREG_OFFSET_CAPTURE, $start)){
		$end = $matches[1][1] + 1;
	}
	else{
		return false;
	}
	unset($matches);
	//echo "Stage 1: Mem usage is: ", memory_get_usage(), "\n";
	
	$bodytxt = substr($html, $start, $end - $start);
	unset($html);

	$bodytxt = strip_tags_content($bodytxt, "script");
	$bodytxt = strip_tags_content($bodytxt, "style");
	$bodytxt = strip_cdata($bodytxt);
	return strip_tags($bodytxt);
}

//returns doc_title
function fetch_to_trec($url, $doc_id, $TREC){
	global $FILE_ROOT;
	$html = file_get_contents($url);
	$title = "";
	if(preg_match("/<title>(.*)<\/title>/isUm", $html, $matches)){
		$title = $matches[1];
	}
	else{
		echo "No match";
	}
	$plaintext = getPlainText($html);
	if(!$plaintext){
		die(err("Could not get plain text for " . $url .""));
	}
	fwrite($TREC, "<DOC>\n<DOCNO>" . $doc_id ."</DOCNO>\n<TEXT>\n");
	fwrite($TREC, "<title>" . $title . "</title>");
	fwrite($TREC, $plaintext);
	fwrite($TREC, "</TEXT>\n</DOC>\n");
	return $title;
}

/* 
The following function removes temporary files that the script uses during processing a request
*/
function clean(){
	global $STORAGE;
	$removeArr = Array(
		fname($STORAGE . "trec_file.list"),
		fname($STORAGE . "trec.txt"),
		fname($STORAGE . "build_index.param"),
		fname($STORAGE . "cluster.param"),
		fname($STORAGE . "index"),
		fname($STORAGE . "sum.param")
		);
	//If you are trying to get a better understanding of how the system works I suggest commenting out the following system(...) line so the temporary files are not removed
	$cmd = "rm -r " . implode($removeArr, " ");
	system($cmd);
}

/*
$docIds		an array of document ids
returns an associative array document id => document url
*/
function getUrlArray($docIds){
	global $cxn;
	$respArr = Array();
	$query = "SELECT `url`, `pageID` FROM pages WHERE `pageID` IN (";
	$first = true;
	foreach($docIds as $val){
		if($first)
			$first = false;
		else
			$query .= ",";
		$query .= intval($val);
	}
	$query .= ")";

	$results = mysqli_query($cxn, $query) or die(err("Could not get page url's from database"));
	if(mysqli_num_rows($results) < sizeof($docIds)){
		die(err("Some documents from your request were not found"));
	}
	while($row = mysqli_fetch_assoc($results)){
		$respArr[$row['pageID']] = $row['url'];
	}
	return $respArr;
}

function table_valid($tbl){
	switch($tbl){
		case "pages":
		case "annotation":
		case "snippet":
		case "bookmarks":
		case "searches":
			return true;
		default:
			return false;
	}
}
?>