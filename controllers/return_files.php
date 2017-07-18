<?php
class returnFiles extends Controller{
      function run($xml){
          global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;
          
          $nameCat = stringval($xml->nameCat);
          
          
          foreach($i = 0; $i < $arrlength; $i++){
            if($nameCat == $arr[i][0]){
            $lengthcolumn = count($arr[i]);
              foreach($j=0; $j < $lengthcolumn; $j++){
                  echo $arr[i][j];
              }
            }else{
                  echo "Category not available";
            }
          
          }
          
          
          }
      
     










}
}




