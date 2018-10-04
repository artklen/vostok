<?php

d()->pages_url_base = '/';

d()->use_page_model = function($url = null) {
	if (!isset($url)) {
		$url = d()->url_path;
	}
	d()->this = d()->Page->find_by('url', ltrim($url, '/'));
	if (d()->this->is_empty) {
		return d()->page_not_found(d()->add(['pages', 'url' => $url]) . 'Страница не существует');
	}
	$reverse_crumbs = [];
	$parent = $this->page;
	while ($parent->ne) {
		$link = $parent->link;
		if ($link === '') {
			$link = $parent->url;
		}
		$reverse_crumbs[] = ['link' => $link, 'title' => $parent->title];
	}
	d()->crumbs_list = array_reverse($reverse_crumbs);
	d()->crumbs_list[] = d()->crumb_for(d()->this, false);
	d()->Seo->from_object();
};

d()->pages_route = function() {
	d()->use_page_model();
	$template = '/pages/' . d()->url_path;
	if (substr($template, -1) === '/') {
		$template .= 'index';
	}
	$template .= '.html';
	$dir = ROOT . '/app'; // по аналогии с классом View
	if (!is_file($dir . $template)) {
		$pos = strrpos($template, '/');
		$template = substr($template, 0, $pos + 1) . 'show.html';
		if (!is_file($dir . $template)) {
			$template = '/pages/show.html';
		}
	}
	return d()->view->render($template);
};

d()->admin_routes_page_url = function() {
	print d()->view->render(strrchr(__DIR__, '/') . '/admin_page_url.html');
};
