<?php
class Example2 extends Controller{
	// Our goal is to go through the resources in the input and simply return
	// the content and url of each one.
	//
	function run($xml){
		global $BIN;
		// The format of our response will be:
		// <root>
		//     <content> content of first resource </content>
		//     <url> url of first resource </url>
		//
		//     <content> content of second resource </content>
		//     <url> url of second resource </url>
		// (so on and so forth...)
		// </root>
		// We start with the opening <root> tag.
		$toReturn = "<root>";
		// We want to go through each resource given, so we save the resource array in a variable.
		$resourceArr = $xml->resourceList->resource;
		// Similar to a python for loop, the foreach loop sets $resource to each item
		// in the $resourceArr array one by one.
		foreach($resourceArr as $resource) {
			// We append to the $toReturn variable the content of the current resource.
			$toReturn = $toReturn . "<resource>";
			$toReturn = $toReturn . "<content>" . $resource->content . "</content>";
			// Now similarly we append the url.
			$toReturn = $toReturn . "<url>" . $resource->url . "</url>";
			$toReturn = $toReturn . "<id>" . $resource->id . "</id>";
			global $BIN;
			// Let's create the command: "python ../bin/test.py argument"
			$filename = "python " . $BIN . "test.py \"" . $resource->content . "\"";
			// Our output will be saved in this array.
			$output = [];
			exec($filename, $output);
			// Output is an array, so let's concatenate all of the output
			// items in case there are multiple lines output.
			$fullOutput = "";
			foreach ($output as $line) {
				$fullOutput = $fullOutput . $line;

			}
			$toReturn = $toReturn . "<output>" . $fullOutput . "</output>";
			$toReturn = $toReturn . "</resource>";
		}
		// Now we close the root tag.
		$toReturn .= "</root>";
		
		return $toReturn;
	}
}