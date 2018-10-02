<?php 

d()->render_field = function($name, $meta = [], $path = '') {
	if (is_array($name)) {
		$args = $name;
		$name = $args[0];
		if (isset($args[1])) {
			$meta = $args[1];
			if (isset($args[2])) {
				$path = $args[2];
			}
		}
	}
	$old_vars = d()->set_vars([
		'field' => $meta,
		'name'  => $name
	]);
	
	$result = '';
	$template = $path . (isset($meta['type']) ? $meta['type']  : '');
	if ($template{0} === '/') {
		$file = __DIR__ . '/..' . $template . '.html';
		if (is_file($file)) {
			$result = d()->view->partial("{$template}.html");
		} else {
			$file = __DIR__ . '/..' . $path . 'default.html';
			if (is_file($file)) {
				$result = d()->view->partial("{$path}default.html");
			}
		}
	} else {
		$result = d()->call("{$template}_tpl");
		if ($result === '') {
			$result = d()->call("{$path}default_tpl");
		}
	}
	d()->set_vars($old_vars);
	return $result;
};

d()->render_fields_list = function($args) {
	if (!isset($args[2])) {
		$names = array_keys($args[0]);
		$meta  = $args[0];
		$path  = $args[1];
	} else {
		$names = $args[0];
		$meta  = $args[1];
		$path  = $args[2];
	}
	
	// определяем, фильтрация по чёрному или белому списку
	$is_blacklist = true;
	foreach ($names as $name) {
		if (isset($meta[$name], $meta[$name]['is_enabled'])) {
			$is_blacklist = false;
			break;
		}
	}
	
	// фильтруем поля
	$filtered_names = [];
	foreach ($names as $name) {
		if (isset($meta[$name]) && ($is_blacklist && (!isset($meta[$name]['is_disabled']) || !d()->check_is_param_true($meta[$name], 'is_disabled')) || !$is_blacklist && !d()->check_is_param_false($meta[$name], 'is_enabled'))) {
			$filtered_names[] = $name;
		}
	}
	
	$result = '';
	foreach ($filtered_names as $name) {
		$result .= d()->render_field($name, $meta[$name], $path);
	}
	return $result;
};


d()->check_is_param_true = function($array, $key) {
	static $values = [1, true, '1', 'yes', 'true'];
	return isset($array, $key, $array[$key]) && in_array($array[$key], $values, true);
};

d()->check_is_param_false = function($array, $key) {
	static $values = [0, false, '0', 'no', 'false'];
	return isset($array, $key, $array[$key]) && in_array($array[$key], $values, true);
};
