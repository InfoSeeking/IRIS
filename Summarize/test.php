<?php
include 'htmlparser.php';
$html = file_get_html('http://politicalticker.blogs.cnn.com/2012/09/07/obama-calls-jobs-report-not-good-enough/');
foreach($html->find('title') as $element);
$f = fopen('./txt/1.txt', 'w');
fwrite($f, $html->plaintext);
fwrite($f, $element);
fclose($f);
?>