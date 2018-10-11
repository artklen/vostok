<?php

function main()
{
	d()->current_city = d()->city_of_subdomain;
	$content = d()->content();
	d()->check_if_seo_prepared();
	d()->content = "$content";
	print d()->render('main_tpl');
	d()->emit('after_main_render');
}

d()->seo_default_params = [
	'title' => function($object) {
		if ($object->page_title !== '') {
			return $object->page_title;
		}
		return d()->full_title_of($object) . ' | ' . d()->Option->common_title;
	},
	'description' => 'page_description',
	'keywords' => 'page_keywords',
	'h1' => function($object) {
		return d()->full_title_of($object);
	},
	'text' => function($object) {
		return $object['text'];
	},
];
