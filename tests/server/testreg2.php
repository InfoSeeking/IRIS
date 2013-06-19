<?php

$urls = Array(
"http://www.bing.com/search?q=european+history&go=&form=QBLH&qs=AS&pq=european+his",
/*"http://coagmentopad.rutgers.edu/project97?nickname=bugs",
"http://www.google.com/search?q=nodejs&ie=utf-8&oe=utf-8&aq=t&rls=org.mozilla:en-US:official&client=firefox-a",
"http://www.google.com/#hl=en&sclient=psy-ab&q=What+is+the+color+of+sky&oq=What+is+the+color+of+sky&aq=f&aqi=g2g-j2&aql=&gs_sm=3&gs_upl=15496l20704l0l21657l28l22l2l4l4l5l942l5665l3.7.7.1.1.0.2l27l0&gs_l=hp.3..0l2j0i18l2.15496l20704l0l21657l28l22l2l4l4l5l942l5665l3j7j7j1j1j0j2l27l0.frgbld.&pbx=1&bav=on.2,or.r_gc.r_pw.r_qf.,cf.osb&fp=128985bf4f873deb&biw=1339&bih=956",
"http://www.google.com/#hl=en&sclient=psy-ab&q=Why+is+the+Sky+blue%3F&oq=Why+is+the+Sky+blue%3F&aq=f&aqi=g4&aql=&gs_sm=3&gs_upl=10544l13977l1l15290l20l15l0l5l5l1l523l3134l3.5.4.2.0.1l20l0&gs_l=hp.3..0l4.10544l13977l1l15290l20l15l0l5l5l1l523l3134l3j5j4j2j0j1l20l0.frgbld.&pbx=1&bav=on.2,or.r_gc.r_pw.r_qf.,cf.osb&fp=128985bf4f873deb&biw=1339&bih=956",
"http://www.sciencemadesimple.com/sky_blue.html",
"http://www.google.com/#hl=en&sclient=psy-ab&q=Why+is+the+Sky+blue%3F&oq=Why+is+the+Sky+blue%3F&aq=f&aqi=g4&aql=&gs_sm=3&gs_upl=10544l13977l1l15290l20l15l0l5l5l1l523l3134l3.5.4.2.0.1l20l0&gs_l=hp.3..0l4.10544l13977l1l15290l20l15l0l5l5l1l523l3134l3j5j4j2j0j1l20l0.frgbld.&pbx=1&bav=on.2,or.r_gc.r_pw.r_qf.,cf.osb&fp=128985bf4f873deb&biw=1339&bih=956",
"http://www.contextminer.org/",
"http://www.cnn.com/",
"http://www.macrumors.com/"*/
);

function strip_tags_content($text, $tagStr) {
	$pattern = "@<(" . $tagStr . ").*>.*</" . $tagStr . ">@isUm";
 	return preg_replace($pattern, "", $text);
}

function strip_cdata($text){
	return preg_replace("@<!\[CDATA\[.*\]\]>@isUm", "", $text);
}

function getPlainText($html){
	$start = 0, $end = 0;
	if(preg_match("@<body.*(>)@iUsm", $html, $matches, PREG_OFFSET_CAPTURE)){
		$start = $matches[1][1] + 1;
	}
	else{
		//return false;
	}
	if(preg_match("@</body>@iUsm", $html, $matches, PREG_OFFSET_CAPTURE)){
		$end = $matches[1][1] + 1;
	}
	else{
		//return false;
	}
	echo "HERE";
	echo $start . ", " . ($end - $start);
	//return substr($html, $start, $end - $start);
	return "HI";
}

echo "HEREa";
foreach($urls as $url){
	$html = file_get_contents($url);
	$html = getPlainText($html);
	echo $html;
	if(!$html){
		echo "Nope";
	}
	else{
		echo htmlspecialchars($html);
	}
	echo "HELLO";
}

