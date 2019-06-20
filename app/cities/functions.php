<?php

d()->singleton('city_of_subdomain', function() {
	$main_domain = $_ENV['SITE_MAIN_DOMAIN'];
	if (substr($main_domain, 0, 4) === 'www.') {
		$main_domain = substr($main_domain, 4);
	}
	$main_domain_length = strlen($_ENV['SITE_MAIN_DOMAIN']);
	if (substr('.' . $_SERVER['HTTP_HOST'], -$main_domain_length - 1) === '.' . $main_domain) {
		$city_part = rtrim(substr($_SERVER['HTTP_HOST'], 0, -$main_domain_length), '.');
	} else {
		// другой домен
		$city_part = '';
	}
	$result = d()->City->find_by('subdomain', $city_part);
	if ($result->is_empty && $city_part === '') {
		$city = d()->City->new;
		$city->subdomain = '';
		$city->save();
		$result = d()->City->find_by('id', $city->insert_id);
	}
	return $result;
});

d()->find_city_by_ip = function() {
	static $SxGeo = null;
	if (!isset($SxGeo)) {
		$SxGeo = new SxGeo(__DIR__ . '/../../vendors/SxGeoCity.dat');
	}
	$city_info = $SxGeo->getCity($_SERVER['REMOTE_ADDR']);
	return d()->City->where('`title`=?', $city_info['city']['name_ru']);
};

// $city_id - необязательный аргумент, старое значение id города, например из cookie
d()->find_current_city = function($city_id = null) {
	$current_city = d()->city_of_subdomain();
	if ($current_city->is_empty) {
		header('Location: ' . d()->root_domain . $_SERVER['REQUEST_URI']);
		exit();
	}
	if (!empty($city_id) && ($city_id !== $current_city->id)) {
		$city = d()->City->find_by('id', $city_id);
		if ($city->ne) {
			if ($city->subdomain !== '' && $current_city->subdomain === '' && d()->is_need_subdomain($_SERVER['REQUEST_URI'])) {
				header('Location: ' . d()->subdomain($city) . $_SERVER['REQUEST_URI']);
				exit();
			} else {
				$current_city = $city;
			}
		}
	}
	return $current_city;
};

d()->is_need_subdomain = function($url = null) {
	return true;
};

d()->subdomain = function($city = null) {
	if (!isset($city)) {
		$city = d()->current_city;
	}
	if ($city !== '' && $city->ne && $city->subdomain !== '') {
		return d()->protocol($city) . '://' . $city->subdomain . '.' . $_ENV['SITE_MAIN_DOMAIN'];
	}
	return d()->root_domain();
};

d()->root_domain = function() {
	return d()->protocol . '://' . $_ENV['SITE_MAIN_DOMAIN'];
};

d()->protocol = function($city = null) {
	return 'https';
};
