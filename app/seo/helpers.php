<?php

d()->seo = function() {
	static $seo;
	if (!isset($seo)) {
		d()->is_seo_prepared = true;
		$seo = new SeoObject();
		$uri = $_SERVER['REQUEST_URI'];
		$seoparam = d()->Seoparam->where('`page_url`=?', $uri);
		if (defined('MULTISITE') && MULTISITE) {
			$seoparam->order('`multi_domain` desc');
		}
		if ($seoparam->ne) {
			$array = $seoparam->to_array;
			$seo->add_source($array[0]);
			d()->admin_add_panel_button('/admin/edit/seoparams/' . $seoparam->id, 'Параметры страницы');
		} else {
			d()->admin_add_panel_button('/admin/edit/seoparams/add?page_url=' . urlencode($_SERVER['REQUEST_URI']), 'Параметры страницы');
		}
	}
	return $seo;
};

d()->seo_from_object = function($object, $params = null) {
	if ($object instanceof ActiveRecord && $object->ne) {
		$array = $object->to_array;
		$array = $array[0];
	} else if (is_array($object) || $object instanceof ArrayAccess) {
		$array = $object;
	} else {
		$array = [];
	}
	if (is_array($params)) {
		$source = [];
		foreach ($params as $name => $param) {
			if ($param instanceof Closure) {
				$value = '' . $param($object);
			} else {
				if (is_numeric($name)) {
					$name = $param;
				}
				$value = isset($array[$param]) ? $array[$param] : '';
			}
			$source[$name] = $value;
		}
	} else {
		$source = $array;
	}
	d()->seo->add_source($source, true);
};

d()->check_if_seo_prepared = function() {
	if (!d()->is_seo_prepared && d()->this) {
		d()->seo_from_object(d()->this, d()->seo_default_params);
	}
};

d()->set_page_title = function($title) {
	if (!d()->crumbs_list) {
		d()->crumbs_list = [['title' => $title]];
	}
	if (d()->seo->h1 === '') {
		d()->seo->h1 = $title;
	}
	if (d()->seo->title === '') {
		d()->seo->title = $title . ' | ' . d()->Option->common_title;
	}
};
