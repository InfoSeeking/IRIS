<?php

class moveFile extends Controller{
    function run($xml){
    global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;
    
    $fileName = $xml->fileName;
    $nameCat = $xml->nameCat;
    category::$arr;
    $arrlength = count($arr);
    
      for($i = 0; $i < $arrlength; $i++){
        if($arr[$i][0] == $nameCat){
        $lengthcolumn = count($arr[$i]);
          for($j = 0; $j < $lengthcolumn; $j++){
            $arr[$i].add($fileName);
          }
        
        }
      
      }
    }
}    
