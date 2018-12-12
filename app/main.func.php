<?php

d()->page_not_found = function() {
	foreach (d()->Seoparam as $item) {
		$urls_str = $item->old_urls;
		$urls = explode("\n", $urls_str);
		foreach ($urls as $url) {
			$url = trim($url);
			if ($_SERVER['REQUEST_URI'] === $url) {
				d()->redirect_301($item->page_url);
			}
		}
	}

	foreach (d()->Seoparam as $item) {
		$urls_str = $item->old_urls;
		$urls = explode("\n", $urls_str);
		foreach ($urls as $url) {
			$url = trim($url);
			if (d()->url_path === $url) {
				d()->redirect_301($item->page_url);
			}
		}
	}

	foreach (d()->Product as $item) {
		$urls_str = $item->old_urls;
		$urls = explode("\n", $urls_str);
		foreach ($urls as $url) {
			$url = trim($url);
			if ($_SERVER['REQUEST_URI'] === $url) {
				d()->redirect_301("/product/".$item->url);
			}
		}
	}

	foreach (d()->Product as $item) {
		$urls_str = $item->old_urls;
		$urls = explode("\n", $urls_str);
		foreach ($urls as $url) {
			$url = trim($url);
			if (d()->url_path === $url) {
				d()->redirect_301("/product/".$item->url);
			}
		}
	}

	foreach (d()->Collection as $item) {
		$urls_str = $item->old_urls;
		$urls = explode("\n", $urls_str);
		foreach ($urls as $url) {
			$url = trim($url);
			if (d()->url_path === $url) {
				d()->redirect_301("/catalog/chasy?collection_id%5B%5D=".$item->id);
			}
		}
	}
	foreach (d()->Collection as $item) {
		$urls_str = $item->old_urls;
		$urls = explode("\n", $urls_str);
		foreach ($urls as $url) {
			$url = trim($url);
			if (d()->url_path === $url) {
				d()->redirect_301("/catalog/chasy?collection_id%5B%5D=".$item->id);
			}
		}
	}

	ob_end_clean();
	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
	header('Status: 404 Not Found');
	d()->content = d()->error_404_tpl();
	print d()->main_tpl();
	exit;
};

function main()
{
	d()->current_city = d()->city_of_subdomain;

	if (!d()->current_city->ne)
	{
		#d()->current_city = d()->City->where('id = ?', 1);
		$url = $_ENV['SITE_MAIN_DOMAIN'];
		# хз почему так не работает
		#d()->redirect($url);die;
		# поэтому сделал так
		header("Location: //$url");die;
	}

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
