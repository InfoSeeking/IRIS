<?php
    class Get_category extends Controller{
	    
	     function __construct(){     
	     }
	     function callCategorize(){
		 include_once __DIR__.'/categorize.php';
		 $categorize = new Categorize();
		 $runcategorize = $categorize->run($xml);
	     }
		    
             function run($xml){
             global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;
             include_once __DIR__.'/categorize.php';
             $file = $xml->filename;
             $arr = Categorize::$arr;
             /*$arrlength = count($arr);*/
	     $arrlength = max(array_map('count',$arr));
             $response = "<parameters>\n<requestID>" . $REQ_ID ."</requestID>\n<requestType>get_category</requestType>";
             
             for($i = 0; $i < $arrlength; $i++){
             $lengthcolumn = count($arr[$i]);
                for($j = 0; $j < $lengthcolumn; $j++){
                    if($arr[$i][$j] == $file){
                    echo $arr[$i][$j];
                    $response .= "<resource><id>" . $arr[$i][$j] . "</id>";
		    $response .= "</resource>";
                    }
                
                }
             
             }
             
            $response .= "</parameters>";
            
            return $response; 
             
             }
    
    }
		$getCategory = new Get_category();
	    	$getCategory->callCategorize();
	
