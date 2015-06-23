<?php
/*
This is an example of using a Java program with IRIS

<parameters>
	<requestType>example_java</requestType>
	<resourceList>
		<resource>
			<table>table name</table>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
*/

class Example_java extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $BIN;
		if(!pe($xml, "resourceList")) die(err("No resources found"));

		//write the XML parameters to a temporary file
		$fname = $STORAGE . $REQ_ID . "_example.xml";
		$TMP = fopen($fname, "w");
		fwrite($TMP, $xml->asXML());
		fclose($TMP);

    //$BIN is the directory containing the C/Java binaries
		$cmd = "java -cp " . $BIN . " Example " . $fname;
		$output = array();
		// exec will run the java program as a terminal would
		// and place the output in the $output array
		exec($cmd, $output);
		unlink($fname);

		// it's up to you how you would like to format the output.
		// The java program can return resources
		$command_response = implode($output);

		//return an XML string as the response
		$response = "<parameters><requestType>extract</requestType><requestID>". $REQ_ID . "</requestID><resourceList>" . $command_response . "</resourceList></parameters>";
		return $response;
	}
}
