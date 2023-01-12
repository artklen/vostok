<?php

d()->clean_phone = function($phone) {
	if (is_array($phone)) {
		$args = $phone;
		$phone = array_shift($args);
	}
	return preg_replace('/\D/', '', $phone);
};

d()->convert_phone = function($phone) {
	return preg_replace(['#^8#', '#[^0-9]#', '#^7#'], ['7', '', '+7'], $phone);
};

d()->phone_href_for = function($value) {
	$value = trim(strtok($value, ','));
	if ($value !== '') {
		$value = 'tel:' . d()->convert_phone($value);
	}
	return $value;
};
