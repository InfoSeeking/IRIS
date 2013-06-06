<?php
/*
this version has the html stripping with regular expressions, it does not work on the server because of the backtrace limit
*/
function err($msg){
	return "<parameters><error><message>$msg</message></error></parameters>";
}

function esc($str){
	global $cxn;
	return mysqli_real_escape_string($cxn, $str);
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
	
	$query = sprintf("INSERT INTO operator_requests (`reqType`) VALUES('%s')", esc($xml->requestType));
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
			$fname = "output/responses/" . $row["reqID"] . ".xml";
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

function strip_tags_content($text, $tagStr) {
	$pattern = "@<(" . $tagStr . ").*>.*</" . $tagStr . ">@isUm";
 	return preg_replace($pattern, "", $text);
}

function strip_cdata($text){
	return preg_replace("@<!\[CDATA\[.*\]\]>@isUm", "", $text);
}

function getPlainText($html){
	if(preg_match("@<body.*>(.+)</body>@ism", $html, $matches)){
		$bodytxt = $matches[1];
		$bodytxt = strip_tags_content($bodytxt, "script");
		$bodytxt = strip_tags_content($bodytxt, "style");
		$bodytxt = strip_cdata($bodytxt);
		return strip_tags($bodytxt);
	}
	else{
		echo "HERE";
		return false;
	}
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
	system("rm -r " . implode($removeArr, " "));
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
?>