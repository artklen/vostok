<?php

d()->time = function() {
	static $result;
	if (!isset($result)) {
		$result = time();
	}
	return $result;
};

d()->set_vars = function($vars = []) {
	$old_vars = [];
	foreach ($vars as $key => $value) {
		$old_vars = d()->$key;
		d()->$key = $value;
	}
	return $old_vars;
};
