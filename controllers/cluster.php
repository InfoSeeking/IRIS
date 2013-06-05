<?php
$ids = Array();
$urls = Array();
$titles = Array();//and this one too

$numOfClusters = intval($xml->numClusters);
$numOfDocuments = 0;

$TREC = fopen($FILE_ROOT . fname("output/trec.txt"), "w");//TODO make sure that you're not overwriting anything with a unique id or something

$TREC_FILE_LIST = fopen($FILE_ROOT . fname("output/trec_file.list"), "w");
fwrite($TREC_FILE_LIST, $FILE_ROOT . fname("output/trec.txt"));
fclose($TREC_FILE_LIST);

foreach($xml->docList->doc as $doc){
	array_push($ids, $doc->docID);
	$numOfDocuments++;
}

$urls = getUrlArray($ids);

foreach($urls as $id => $url){
	$titles[$id] = fetch_to_trec($url, $id, $TREC);
}

fclose($TREC);

if($numOfClusters > $numOfDocuments){
	die(err("Number of clusters is more than the number of documents"));
}

//now build the index
$IPARAM = fopen(fname("output/build_index.param"), "w");
$tmp = "<parameters><index>" . fname("output/index") . "</index><indexType>indri</indexType><dataFiles>" . fname("output/trec_file.list") . "</dataFiles><docFormat>trec</docFormat><stopwords>stopwords.param</stopwords></parameters>";
fwrite($IPARAM, $tmp);
fclose($IPARAM);

system($FILE_ROOT . "bin/BuildIndex " . fname("output/build_index.param") . $cmd_extra);

//create cluster parameters
$CPARAM = fopen($FILE_ROOT . fname("output/cluster.param"), "w");

fwrite($CPARAM, "<parameters>\n<index>" . fname("output/index") . "</index>\n<docMode>max</docMode>\n<numParts>" . $numOfClusters . "</numParts>\n</parameters>\n");
fclose($CPARAM);

//do clustering
$out = Array();

exec($FILE_ROOT . "bin/OfflineCluster " . fname("output/cluster.param"), $out);

$response = "<parameters>\n<requestID>" . $REQ_ID ."</requestID>\n<requestType>cluster</requestType>\n<clusterList>\n";


//get the document id's of each cluster by parsing the output of the last function (pray to god it works)
for($i = 0; $i < $numOfClusters; $i++){
	//line we need is line 2
	$arr =  explode(": ",$out[$i + 1]);
	$line = $arr[1]; //php 5.4 supports this
	$cids = explode(" ", $line);//cids is cluster ids

	//echo the result
	$response .= "<cluster><clusterID>" . $i ."</clusterID>";
	"<docList>\n";
        for($j = 0; $j < sizeof($cids); $j++){
        	$response .= "<doc><docID>" . $cids[$j] ."</docID>";
        	//$response .= "<url>TODO</url>";
        	$response .= "<title>" . $titles[$cids[$j]] . "</title>";
           	$response .= "</doc>";
        }
      $response .= "</cluster>";
}

$response .= "</clusterList></parameters>";