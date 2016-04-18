<?php
class Example extends Controller{
	function run($xml){
		global $BIN;

		// $toReturn="<root>";
		// $resourceArr=$xml->resourceList->resource;
		// foreach($resourceArr as $resource) {
		// 	$toReturn = $toReturn . "<content>" . $resource->content . "</content>";
		// 	$toReturn = $toReturn . "<url>" . $resource->url . "</url>";
		// }

		// $toReturn .="</root>";
		// return $toReturn;

		$filename= "python " . $BIN . "test.py arguement";
		$output = [];

		exec($filename, $output);

		return "<output>". $output[0] . "</output>";

	}
}