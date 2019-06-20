<?php

d()->route('/generate_sitemap', function() {
	set_time_limit(0);
	d()->generate_sitemap();
	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found'); 
	header('Status: 404 Not Found');
	exit;
});

d()->on('cron.daily', function() {
	set_time_limit(0);
	d()->generate_sitemap();
	print ' sitemap OK ';
});

d()->route('/sitemap.xml', function() {
	header('X-Accel-Expires: 0');
	$host = $_SERVER['HTTP_HOST'];
	if (substr($host, 0, 4) === 'www.') {
		$host = substr($host, 4);
	}
	$file = ($host === $_ENV['SITE_MAIN_DOMAIN']) ? 'sitemap_' : 'sitemap_' . strtok($host, '.');
	header('Content-type:application/xml; charset=utf-8');
	print file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/sitemaps/' . $file . '.xml');
	exit;
});

d()->generate_sitemap = function() {
	/* дополнительные параметры */
	/* возможные значения changefreq: always, hourly, daily, weekly, monthly, yearly, never */
	/* возможные значения priority: от 0.0 до 1.0 включительно */
	$c = [
	
		// страницы
		'index' => [
			'changefreq' => 'daily',
			'priority' => 1,
		],
		'pages' => [
			'changefreq' => 'weekly',
			'priority' => 0.9,
		],
		
		// категории
		'categories' => [
			'changefreq' => 'weekly',
			'priority' => 0.9,
		],
		
		// товары
		'products' => [
			'changefreq' => 'weekly',
			'priority' => 0.8,
		],
		
		// вопросы
		'faq_categories' => [
			'changefreq' => 'weekly',
			'priority' => 0.6,
		],
		
		// рубрики новостей
		'rubrics' => [
			'changefreq' => 'weekly',
			'priority' => 0.4,
		],
		
		// новости
		'news' => [
			'changefreq' => 'weekly',
			'priority' => 0.7,
		],
	];
	
	/* чёрные списки */
	$pages_black_list = ['/', '/search/', '/thankyou', '/thankyou-reviews', '/basket', '/catalog', '/product', ];
	
	/* по городам */
	foreach (d()->City->all as $city) {
		$generator = new SitemapGenerator();
		$generator->files_names_template = 'sitemap_' . $city->subdomain;
		$generator->sitemap_index_name   = 'sitemap_' . $city->subdomain;
		$generator->url_prefix           =  d()->protocol($city) . '://' . $city->domain;
		
		/* страницы */
		$generator->add_url('/', substr(d()->Page->find_by('url', '')->updated_at, 0, 10), $c['index']['changefreq'], $c['index']['priority']);
		foreach (d()->Page as $item) {
			$url = d()->url_for($item);
			if (!in_array($url, $pages_black_list)) {
				$generator->add_url($url, substr($item->updated_at, 0, 10), $c['pages']['changefreq'], $c['pages']['priority']);
			}
		}
		
		/* категории */
		foreach (d()->Category as $item) {
			$url = d()->url_for($item);
			$generator->add_url($url, substr($item->updated_at, 0, 10), $c['categories']['changefreq'], $c['categories']['priority']);
		}
		
		/* товары */
		foreach (d()->Product as $item) {
			$url = d()->url_for($item);
			$generator->add_url($url, substr($item->updated_at, 0, 10), $c['products']['changefreq'], $c['products']['priority']);
		}
		
		/* вопросы */
		foreach (d()->Faq_category as $item) {
			$url = d()->url_for($item);
			$generator->add_url($url, substr($item->updated_at, 0, 10), $c['faq_categories']['changefreq'], $c['faq_categories']['priority']);
		}
		
		/* рубрики новостей */
		foreach (d()->Rubric as $item) {
			$url = d()->url_for($item);
			$generator->add_url($url, substr($item->updated_at, 0, 10), $c['rubrics']['changefreq'], $c['rubrics']['priority']);
		}
		
		/* новости */
		foreach (d()->News as $item) {
			$url = d()->url_for($item);
			$generator->add_url($url, substr($item->updated_at, 0, 10), $c['news']['changefreq'], $c['news']['priority']);
		}
		
		$generator->generate_index();
	}

};
