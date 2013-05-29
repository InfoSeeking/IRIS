<?php
include 'htmlparser.php';
$html = file_get_html('http://online.wsj.com/article/SB10000872396390443524904577650993884618770.html?mod=WSJ_Tech_LEFTTopNews');
foreach($html->find('title') as $element);
$f = fopen('./txt/7.txt', 'w');
fwrite($f, $element);
fwrite($f, $html->plaintext);


fclose($f);
?>