<?php

d()->crumbs = function() {
	if (d()->crumbs_list === '') {
		if (!empty(d()->this)) {
			$t = d()->this;
			if ($t instanceof ActiveRecord) {
				$crumbs = $t->crumbs;
				if (empty($crumbs) && (($title = $t->title) !== '')) {
					$crumbs = [['title' => $title]];
				}
			}
			if (empty($crumbs) && is_array($t) && isset($t['title'])) {
				$crumbs = [['title' => $t['title']]];
			}
		}
		if (empty($crumbs)) {
			$crumbs = [];
		}
		d()->crumbs_list = $crumbs;
	}
	return d()->view->render('/app/components/crumbs.html');
};

d()->page_crumb = function($url, $is_link = true) {
	return ['link' => $is_link ? $url : null, 'title' => d()->Page->find_by('url', ltrim($url, '/'))->title];
};

d()->crumb_for = function($arg, $is_link = true) {
	if ($arg instanceof ActiveRecord) {
		return $arg->ne ? [
			'link' => $is_link ? d()->url_for($arg) : null,
			'title' => $arg->title
		] : null;
	} else if (is_object($arg)) {
		return [
			'link' => $is_link && isset($arg->link) ? $arg->link : null,
			'title' => isset($arg->title) ? $arg->title : null,
		];
	} else if (is_array($arg)) {
		return [
			'link' => $is_link && isset($arg['link']) ? $arg['link'] : null,
			'title' => isset($arg['title']) ? $arg['title'] : null,
		];
	}
	return null;
};

d()->tree_crumbs = function($arg, $is_link = true) {
	$crumbs_list = [d()->crumb_for($arg, $is_link)];
	for ($item = $arg->parent(), $ids_set = [$arg->id => true]; $item->ne && empty($ids_set[$item->id]); $ids_set[$item->id] = true, $item = $item->parent()) {
		$crumbs_list[] = d()->crumb_for($item);
	}
	return array_reverse($crumbs_list);
};

