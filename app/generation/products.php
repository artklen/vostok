<?php

d()->products_seo_params = [
	'title' => function($object) {
		return d()->full_title_of($object) . ' - ' . t('купить') . ' ' . d()->current_city->second_title . ' | ' . d()->Option->common_title;
	},
];
