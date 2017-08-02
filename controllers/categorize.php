<?php
class Categorize extends Controller{
           function run($xml){
                  global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;
                  if(!pe($xml, "resourceList")) die(err("No resources found"));
                  
                  $numCategories = intval($xml->numCategories);
                  static $arr = array();
                  /*self::$arr = array();*/
                  
                  for($i=0;$i < numCategories; $i++){
                      $name = intval($xml->nameCat);
                             if($i=0){
                                $arr[0] = $name;
                             }else{
                                $arr.add($name);
                             }
                             
                  }
                  $i = 0;
                  while($i < numCategories){
                      $numDoc = intval($xml->numDoc);
                           $k = 0;
                           foreach($xml->resourceList2->resource2 as $res){
                                      $arr[i][k] = $res;
                                      $k++;
                           }
                       $i++;         
                  }
                  
                 $response .= "<parameters><requestType>categorize</requestType></paramaters>" . "Done!";
     
                 return $response;
           }
}
