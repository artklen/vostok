<?php

d()->redirect_301 = function($url) {
	if ($url === '' || $url{0} === '/') {
		$url = 'http://' . $_SERVER['HTTP_HOST'] . $url; // https?
	}
	header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
	header('Location: ' . $url);
	exit();
};

d()->page_not_found = function($message = '') {
	if (!empty(d()->old_url_handlers)) {
		foreach (d()->old_url_handlers as $handler) {
			if ($handler_result = $handler()) {
				return $handler_result;
			}
		}
	}
	ob_end_clean();
	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found'); 
	header('Status: 404 Not Found');
	d()->message = $message;
	d()->content = d()->error_404_tpl();
	print d()->main_tpl();
	exit;
};

d()->singleton('url_path', function() {
	return strtok($_SERVER['REQUEST_URI'], '?');
});

d()->full_title_of = function($arg) {
	return $arg->full_title !== '' ? $arg->full_title : $arg->title;
};
