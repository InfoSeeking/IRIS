<?php

class MoveFile extends Controller{
    function run($xml){
    global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;
    
    $fileName = $xml->fileName;
    $nameCat = $xml->nameCat;
    category::$arr;
    $arrlength = count($arr);
    
      for($i = 0; $i < $arrlength; $i++){
        if($arr[$i][0] == $nameCat){
        $lengthcolumn = count($arr[$i]);
          
            $arr[$i][$lengthcolumn] = $fileName;
          
        
        }
      
      }
    }
    $response = "<parameters><requestType>move_file</requestType>". "Done!";
    return $response;
}    
