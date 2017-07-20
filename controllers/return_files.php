<?php
class returnFiles extends Controller{
      function run($xml){
          global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;
          
          $nameCat = stringval($xml->nameCat);
          category::$arr;
          $arrlength = count($arr);
          
          for($i = 0; $i < $arrlength; $i++){
            if($nameCat == $arr[i][0]){
            $lengthcolumn = count($arr[i]);
              for($j=0; $j < $lengthcolumn; $j++){
                  echo $arr[i][j];
              }
            }else{
                  echo "Category not available";
            }
          
          }
          
          
          }
      
     










}
}




