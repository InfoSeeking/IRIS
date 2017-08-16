<?php
class Categorize extends Controller{
           public static $arr = array();
           function run($xml){
                  global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;
                  
                  $numCategories = intval($xml->numCategories);
                  self::$arr;
                  /*self::$arr = array();*/
                      
                  /*if(!pe($xml, "resourceList")) die(err("No resources found"));*/
                      
                  for($i=0;$i < $numCategories; $i++){
                      $name = intval($xml->nameCat);
                             if($i=0){
                                self::$arr[$i][0] = $name;
                             }else{
                                self::$arr[$i][0] = $name;
                             }
                         $response = "<parameters><requestType>categorize</requestType><requestID>". $REQ_ID . "</requestID><resourceList>" . self::$arr[$i][0] . "</resourceList>"; 
                  }
                  $j = 0;
                  while($j < $numCategories){
                      $numDoc = intval($xml->numDoc);
                           $k = 0;
                           foreach($xml->resourceList->resource as $res){
                                      self::$arr[$j][$k] = $res->url;
                                      $k++;
                           $response .= "<resourceList>" . self::$arr[$j][$k] . "</resourceList>";
                           }
                       $j++;         
                  }
                 $output = "Done!";
                 /*$response = "<parameters><requestType>categorize</requestType><requestID>". $REQ_ID . "</requestID><resourceList>". $output . "</resourceList></parameters>";*/
                 $response .= "</parameters>"; 
     
                 return $response;
           }
}
