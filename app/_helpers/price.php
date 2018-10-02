<?php

d()->price_format = function($arg, $default = null) {
	if (is_array($arg)) {
		$args = $arg;
		$arg = array_shift($args);
		if (!empty($args)) {
			$default = array_shift($args);
		}
	}
	$arg = 1.0 * $arg;
	if ($arg < 1e-7 && isset($default)) {
		return $default;
	}
	$result = number_format($arg, 2, ',', ' ');
	if (substr($result, -3) === ',00') {
		$result = substr($result, 0, -3);
	}
	return $result;
};
