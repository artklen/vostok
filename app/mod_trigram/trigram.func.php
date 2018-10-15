<?php

function get_trigram($str)
{
	$str = mb_strtolower($str, 'UTF-8');
	$str = preg_replace('#[\\\'\"\-\s\t\(\)\.«»]#', '', $str);
	//$str = '__' . $str . '__';
    $array = array();
	$strlen = mb_strlen($str);
    while ($strlen) { 
        $array[] = mb_substr($str, 0, 1, 'UTF-8'); 
        $str = mb_substr($str, 1, $strlen, 'UTF-8'); 
        $strlen = mb_strlen($str);
    }
	$res = array();
	for ($i = 1, $c = count($array) - 2; $i <= $c; $i++) {
		$res[] = $array[$i - 1] . $array[$i] . $array[$i + 1];
	}
    return $res;
}
