<?php
clean();

//eventually make this an associative array
$urls = Array();
$numOfClusters = intval($xml->numClusters);
$numOfDocuments = 0;
$i = 1;//dummy id's
$TREC = fopen($FILE_ROOT . "output/trec.txt", "w");//TODO make sure that you're not overwriting anything with a unique id or something

$TREC_FILE_LIST = fopen($FILE_ROOT . "output/trec_file.list", "w");
fwrite($TREC_FILE_LIST, $FILE_ROOT . "output/trec.txt");
fclose($TREC_FILE_LIST);

foreach($xml->docList->doc as $doc){
	array_push($urls, $doc->url);
	fetch_to_trec($doc->url, $i, $TREC, $FLIST);
	$i++;
	$numOfDocuments++;
}

if($numOfClusters > $numOfDocuments){
	die(err("Number of clusters is more than the number of documents"));
}

//now build the index`

system($FILE_ROOT . "bin/BuildIndex output/buildindex.param 2>&1");
//create cluster parameters
$CPARAM = fopen($FILE_ROOT . "output/cluster.param", "w");

fwrite($CPARAM, "<parameters>\n<index>output/clusterIndex</index>\n<docMode>max</docMode>\n<numParts>" . $numOfClusters . "</numParts>\n</parameters>\n");
fclose($CPARAM);

//do clustering
$out = Array();
exec($FILE_ROOT . "bin/OfflineCluster output/cluster.param 2>&1", $out);

$response = "<parameters>\n<requestID>TODO</requestID>\n<requestType>cluster</requestType>\n<clusterList>\n";

//get the document id's of each cluster by parsing the output of the last function (pray to god it works)
for($i = 0; $i < $numOfClusters; $i++){
	//line we need is line 2
	$line = explode(": ",$out[$i + 1])[1];
	$ids = explode(" ", $line);

	//echo the result
	$response .= "<cluster><clusterID>" . $i ."</clusterID>";
	"<docList>\n";
        for($j = 0; $j < sizeof($ids); $j++){
        	$response .= "<doc><docID>" . $ids[$j] ."</docID>";
        	$response .= "<url>TODO</url>";
           	$response .= "</doc>";
        }
      $response .= "</cluster>";

}

$response .= "</clusterList></parameters>";

echo $response;
