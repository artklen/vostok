<?php

d()->redirect_301 = function($url) {
	if ($url === '' || $url{0} === '/') {
		$url = 'http://' . $_SERVER['HTTP_HOST'] . $url; // https?
	}
	header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
	header('Location: ' . $url);
	exit();
};

d()->singleton('url_path', function() {
	return strtok($_SERVER['REQUEST_URI'], '?');
});

d()->full_title_of = function($arg) {
	return $arg->full_title !== '' ? $arg->full_title : $arg->title;
};
