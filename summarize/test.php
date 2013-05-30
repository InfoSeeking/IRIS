<?php
include 'htmlparser.php';
$html = file_get_html('http://www.westegg.com/bacon/nobility.html');
foreach($html->find('title') as $element);
$f = fopen('./txt/3.txt', 'w');
fwrite($f, $html->plaintext);
fwrite($f, $element);
fclose($f);
?>