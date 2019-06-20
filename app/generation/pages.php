<?php

d()->pages_seo_params = [
	'title' => function($object) {
		if (d()->url_path === '/') {
			$result = d()->full_title_of($object);
			if (d()->current_city->subdomain !== '') {
				$result .= ' ' . d()->current_city->second_title;
			}
			$result .= ' | ' . d()->Option->common_title;
			return $result;
		}
		return d()->seo_default_params['title']($object);
	},
];
