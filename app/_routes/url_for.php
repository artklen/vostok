<?php

d()->url_for = function($arg) {
	//if (is_array($arg)) { // возможно не пригодится
	//	$arg = reset($arg);
	//}
	if ($arg instanceof ActiveRecord) {
		$link = $arg->link;
		if ($link === '') {
			$link = $arg->url;
		}
		$type = $arg->_options['table'];
	} else if (is_object($arg)) {
		$link = isset($arg->link) ? $arg->link : '';
		$type = isset($arg->table) ? $arg->table : '';
	} else if (is_array($arg)) {
		$link = isset($arg['link']) ? $arg['link'] : '';
		$type = isset($arg['table']) ? $arg['table'] : '';
	}
	return d()->{"{$type}_url_base"} . $link;
};

d()->h_url_for = function($arg) {
	return h(d()->url_for($arg));
};
