<?php

d()->get('/cities/select', function() {
	d()->set_page_title('Выбор города');
	d()->cities_list = d()->City->where('`is_global`=0')->order('`title`');
	d()->current_url_path = $_GET['url'];
	$result = d()->view->render('/cities/select.html');
	if (AJAX) {
		print $result;
		exit;
	}
	return $result;
});

d()->get('/cities/ajax_search', function() {
	$city_query_part = isset($_GET['params'], $_GET['params']['city_id']) ? ' and `city_id`=' . e($_GET['params']['city_id']) : '';
	$data = DBImport::read_all('select `id`, `title`, `realty_region_id`, `realty_area_id`, `kladr_type` from `realty_cities` where `title` like "%' . substr(e($_GET['q']), 1, -1) . '%"' . $city_query_part . ' order by `kladr_type`<>"Город", `title`' . ($_GET['limit'] ? ' limit ' . (1 * $_GET['limit']) : ''));
	if (isset($_GET['limit']) && count($data) < $_GET['limit'] / 3 + 1e-7) {
		$conditions = [];
		if ($data) {
			$conditions[] = '`id` not in (' . implode(',', array_keys($data)) . ')';
		}
		if (isset($_GET['params'], $_GET['params']['city_id'])) {
			$conditions[] = '`city_id`=' . e($_GET['params']['city_id']);
		}
		$conditions_str = $conditions ? ' where ' . implode(' and ', $conditions) : '';
		$data = array_merge($data, DBImport::read_all('select `id`, `title`, `realty_region_id`, `realty_area_id`, `kladr_type` from `realty_cities`' . $conditions_str . ' order by `kladr_type`<>"Город", `title` limit ' . (1 * $_GET['limit'] - count($data))));
	}
	$realty_regions_ids = [];
	$realty_areas_ids = [];
	$realty_kladr_types = [];
	foreach ($data as $item) {
		if ($item['realty_region_id']) {
			$realty_regions_ids[$item['realty_region_id']] = $item['realty_region_id'];
		}
		if ($item['realty_area_id']) {
			$realty_areas_ids[$item['realty_area_id']] = $item['realty_area_id'];
		}
		if ($item['realty_area_id']) {
			$realty_kladr_types[$item['kladr_type']] = $item['kladr_type'];
		}
	}
	$realty_regions_data = $realty_regions_ids ? DBImport::read_all('select `id`, `title` from `realty_regions` where `id` in (' . implode(',', array_map('e', $realty_regions_ids)) . ')') : [];
	$realty_areas_data = $realty_areas_ids ? DBImport::read_all('select `id`, `title` from `realty_areas` where `id` in (' . implode(',', array_map('e', $realty_areas_ids)) . ')') : [];
	foreach ($data as &$item) {
		$item['realty_region_title'] = ($item['realty_region_id'] && isset($realty_regions_data[$item['realty_region_id']])) ? $realty_regions_data[$item['realty_region_id']]['title'] : '';
		$item['realty_area_title'] = ($item['realty_area_id'] && isset($realty_areas_data[$item['realty_area_id']])) ? $realty_areas_data[$item['realty_area_id']]['title'] : '';
		unset($item['realty_region_id'], $item['realty_area_id']);
	}
	unset($item);
	print json_encode(array_values($data), JSON_UNESCAPED_UNICODE);
	exit;
});

d()->get('/robots.txt', function() {
	header('Content-type:text/plain; charset=utf-8');
	$city = d()->city_of_subdomain;
	$content = d()->Option->robots_txt;
	$protocol = d()->protocol($city);
	$sandbox = function($content) use ($city, $protocol) {
		return str_replace('&quot;', '"', eval('return "' . str_replace('"', '&quot;', $content) . '";'));
	};
	print $sandbox($content);
	exit;
});

d()->detect_city = function($ip = null) {
	static $SxGeo = null;
	if (!isset($ip)) {
		$ip = $_ENV['REMOTE_ADDR'];
	}
	if (!isset($SxGeo)) {
		$SxGeo = new SxGeo($_SERVER['DOCUMENT_ROOT'] . '/vendors/SxGeoCity.dat');
	}
	$city_info = $SxGeo->getCityFull($ip);
	$region = $city_info['region']['name_ru'];
	if ($region == "Москва") {
		$region = "Московская область";
	}
	return d()->City->find_by('geo_title', $region);
};
