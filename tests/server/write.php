<?php

$HANDLE = fopen("../output/test.txt", "w");
fwrite($HANDLE, "test");
fclose($HANDLE);

echo "File written";
?>