<?php

d()->less = function($name) {
	if (is_array($name)) {
		$name = reset($name);
	}
	$file_in = "$_SERVER[DOCUMENT_ROOT]/less/$name.less";
	$url_out = "/css/$name.less.temp.css";
	$file_out = $_SERVER['DOCUMENT_ROOT'] . $url_out;
	if (!file_exists($file_out) || filemtime($file_in) > filemtime($file_out)) {
		lessc::ccompile($file_in, $file_out); 
		chmod($file_out, 0777);
	}
	return '<link rel="stylesheet" type="text/css" href="' . h($url_out) . '" />';
};
