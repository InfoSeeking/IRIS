<?php
class Categorize extends Controller{
           function run($xml){
                  global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;
                  
                  
                  $numCategories = intval($xml->numCategories);
                  static $arr = array();
                  /*self::$arr = array();*/
                      
                  if(!pe($xml, "resourceList")) die(err("No resources found"));
                      
                  for($i=0;$i < $numCategories; $i++){
                      $name = intval($xml->nameCat);
                             if($i=0){
                                $arr[0][0] = $name;
                             }else{
                                $arr[$i][0] = $name;
                             }
                             
                  }
                  $j = 0;
                  while($j < $numCategories){
                      $numDoc = intval($xml->numDoc);
                           $k = 0;
                           foreach($xml->resourceList->resource as $res){
                                      $arr[$j][$k] = $res;
                                      $k++;
                           }
                       $j++;         
                  }
                 $output = "Done!";
                 $response = "<parameters><requestType>categorize</requestType><requestID>". $REQ_ID . "</requestID><resourceList>". $output . "</resourceList></parameters>";
     
                 return $response;
           }
}
