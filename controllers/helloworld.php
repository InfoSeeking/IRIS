<?php
class Helloworld extends Controller{
	function run($xml){
		$contentOfFirst = $xml->resourceList->resource[0]->content;

		// Do some processing on content. 
		return "<content>". $contentOfFirst ."</content>";

		//return "<Hello> 
		//<num> ". (2*$xml->numWords). "</num>
		//</Hello>";
	}
}