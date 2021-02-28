<?php

function encodeLink($link) {
	return str_replace(" ", "-", strtolower($link));
}

function decodeLink($link) {
	return str_replace("-", " ", ucwords($link));
}

function numberToAlphabet($n) {
	// start at AAA for 1
	$n = $n + 701;//25 + 26 * 26;
    $r = '';
    for ($i = 1; $n >= 0 && $i < 10; $i++) {
        $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
        $n -= pow(26, $i);
    }
    return $r;
}

function alphabetToNumber($a) {
    $r = 0;
    $l = strlen($a);
    for ($i = 0; $i < $l; $i++) {
        $r += pow(26, $i) * (ord($a[$l - $i - 1]) - 0x40);
    }
	// expect AAA to be 1
    return $r - 702;//-(26 + 26 * 26);
}

function isURL($url) {
	// thanks to daring fireball
	$regex = '#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))#i';
	$matches = preg_match($regex, $url);
	if ($matches == 1)
		return true;
	return false;
	
}

function convertArrayURL($arrTitles, $arrURLs) {
	// simplify arrays
	$simpleTitles = array();
	foreach ($arrTitles as $title) {
		array_push($simpleTitles, $title);
	}
	$simpleURLs = array();
	foreach ($arrURLs as $url) {
		array_push($simpleURLs, $url);
	}
	$arr = array();
	$l = count($simpleTitles);
	for ($i = 0; $i < $l; $i++) {
		if ($simpleTitles[$i] != "" && isURL($simpleURLs[$i]))
			array_push($arr, array($simpleTitles[$i], $simpleURLs[$i]));
	}
	return $arr;
}
?>
