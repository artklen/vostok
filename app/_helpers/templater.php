<?php

// [$params,] $key[, $value]
function selected($args) {
	$params = (!empty($args) && is_array($args[0])) ? array_shift($args) : $_GET;
	$key = str_replace('.', '_', array_shift($args));
//	$value = !empty($args) ? array_shift($args) : '';

	$strs = array();
	$pos0 = 0;
	while (($pos1 = strpos($key, '[', $pos0)) !== false) {
		$str = substr($key, $pos0, $pos1 - $pos0);
		if (substr($str, -1) == ']') {
			$str = substr($str, 0, -1);
		}
		$strs[] = $str;
		$pos0 = $pos1 + 1;
	}
	$str = substr($key, $pos0);
	if (substr($str, -1) == ']') {
		$str = substr($str, 0, -1);
	}
	$strs[] = $str;
	
	$is_set = false;
	$ptr = &$params;
	foreach ($strs as $str) {
		if (strlen($str)) {
			$ptr = &$ptr[$str];
		} else {
			$is_set = true;
			break;
		}
	}
	if (!empty($args)) {
		$value = array_shift($args);
		$result = $is_set ? in_array($value, $ptr) : ($ptr == $value);
	} else {
		$result = $is_set ? !empty($ptr) : isset($ptr);
	}
	
	print $result ? 'selected="selected"' : '';
}

// [$params,] $key[, $value]
function checked($args) {
	$params = (!empty($args) && is_array($args[0])) ? array_shift($args) : $_GET;
	$key = str_replace('.', '_', array_shift($args));
//	$value = !empty($args) ? array_shift($args) : '';

	$strs = array();
	$pos0 = 0;
	while (($pos1 = strpos($key, '[', $pos0)) !== false) {
		$str = substr($key, $pos0, $pos1 - $pos0);
		if (substr($str, -1) == ']') {
			$str = substr($str, 0, -1);
		}
		$strs[] = $str;
		$pos0 = $pos1 + 1;
	}
	$str = substr($key, $pos0);
	if (substr($str, -1) == ']') {
		$str = substr($str, 0, -1);
	}
	$strs[] = $str;
	
	$is_set = false;
	$ptr = &$params;
	foreach ($strs as $str) {
		if (strlen($str)) {
			$ptr = &$ptr[$str];
		} else {
			$is_set = true;
			break;
		}
	}
	if (!empty($args)) {
		$value = array_shift($args);
		$result = $is_set ? in_array($value, $ptr) : ($ptr == $value);
	} else {
		$result = $is_set ? !empty($ptr) : isset($ptr);
	}
	
	print $result ? 'checked="checked"' : '';
}

function checked_if($condition) {
	if(is_array($condition)){
		$condition = $condition[0];
	} 
	return $condition ? 'checked="checked"' : '';
}

function selected_if($condition) {
	if(is_array($condition)){
		$condition = $condition[0];
	} 
	return $condition ? 'selected="selected"' : '';
}

function active_if($condition, $text = 'active') {
	if (is_array($condition)) {
		$tmp0 = array_shift($condition);
		if (!empty($condition)) {
			$text = array_shift($condition);
		}
		$condition = $tmp0;
	} 
	return $condition ? $text : '';
}

d()->if = function($condition, $then = '', $else = '') {
	if (is_array($condition)) {
		$tmp = array_shift($condition);
		if ($condition) {
			$then = array_shift($condition);
		}
		if ($condition) {
			$else = array_shift($condition);
		}
		$condition = $tmp;
	} 
	return $condition ? $then : $else;
};

d()->hide_if = function($condition) {
    if (is_array($condition)) {
        $condition = reset($condition);
    }
    return $condition ? ' style="display:none" ' : '';
};

d()->show_if = function($condition) {
    if (is_array($condition)) {
        $condition = reset($condition);
    }
    return $condition ? '' : ' style="display:none" ';
};
