<?php
include 'htmlparser.php';
$html = file_get_html('http://en.wikipedia.org/wiki/Vijayanagara_Empire');
foreach($html->find('title') as $element);
$f = fopen('./txt/4.txt', 'w');
fwrite($f, $element);
fwrite($f, $html->plaintext);


fclose($f);
?>