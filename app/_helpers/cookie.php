<?php

d()->set_site_cookie = function($key, $value) {
	$_COOKIE[$key] = $value;
	setcookie($key, $value, d()->time + 31536000, '/', $_ENV['SITE_MAIN_DOMAIN']);
};
