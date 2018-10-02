<?php

function valid_email_or_empty($value, $params) {
	if ("$value" === '') {
		return true;
	}
	return valid_email($value, $params);
}

function valid_phone($value) {
	return !!preg_match('#^\+7[0-9]{10}$#', d()->convert_phone($value));
}

function check_no_letters($value) {
	return !preg_match('#\p{L}#', $value);
}
