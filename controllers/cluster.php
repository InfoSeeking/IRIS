<?php
/* uses updated syntax and stuff */
//TODO: add content/url to output if available
class Cluster extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;

		$numOfClusters = intval($xml->numClusters);
		$numOfDocuments = 0;


		$TREC = fopen(fname($STORAGE . "trec.txt"), "w");
		foreach($xml->resourceList->resource as $res){
				//I am not sure why, but if you don't include the newline characters, IndriBuildIndex says the document is malformed :/
				fwrite($TREC, "<DOC>\n<DOCNO>" . $res->id ."</DOCNO>\n<TEXT>\n");
				fwrite($TREC, $res->content);
				fwrite($TREC, "</TEXT>\n</DOC>\n");
				$numOfDocuments++;
		}
		fclose($TREC);


		if($numOfClusters > $numOfDocuments){
			die(err("Number of clusters is more than the number of documents"));
		}

		//now build the index
		$IPARAM = fopen(fname($STORAGE . "build_index.param"), "w");
		$tmp = "<parameters><index>" . fname($STORAGE . "index") . "</index><corpus><path>" . fname($STORAGE . "trec.txt") . "</path><class>trectext</class></corpus><stopper>" . $stopwords . "</stopper></parameters>";
		fwrite($IPARAM, $tmp);
		fclose($IPARAM);

		system($BIN . "IndriBuildIndex " . fname($STORAGE . "build_index.param") . $CMD_EXTRA);
		//create cluster parameters
		$CPARAM = fopen($FILE_ROOT . fname($STORAGE . "cluster.param"), "w");

		fwrite($CPARAM, "<parameters>\n<index>" . fname($STORAGE . "index") . "</index>\n<clusterType>centroid</clusterType>\n<numParts>" . $numOfClusters . "</numParts>\n</parameters>\n");
		fclose($CPARAM);

		//do clustering
		$out = Array();

		exec($BIN . "OfflineCluster " . fname($STORAGE . "cluster.param"), $out);

		$response = "<parameters>\n<requestID>" . $REQ_ID ."</requestID>\n<requestType>cluster</requestType>\n<clusterList>\n";

		//get the document id's of each cluster by parsing the output of the last function (pray to god it works)
		$i = 0;
		for($i = 0; $i < $numOfClusters; $i++){
			//line we need is line 2
			$arr =  explode(": ",$out[$i + 1]);
			$line = $arr[1]; //php 5.4 supports this
			$cids;
			if($line == ""){
				//add a blank
				$response .= "<cluster><clusterID>" . $i . "</clusterID><resource><id>-1</id></resource></cluster>";
				continue;
			}
			$cids = explode(" ", $line);//cids is cluster ids

			//echo the result
			$response .= "<cluster><clusterID>" . $i ."</clusterID><resourceList>\n";
		        for($j = 0; $j < sizeof($cids); $j++){
		        	$response .= "<resource><id>" . $cids[$j] ."</id>";
		          	$response .= "</resource>";
		   	    }
		     $response .= "</resourceList></cluster>";
		}


		$response .= "</clusterList></parameters>";

		return $response;
	}
}