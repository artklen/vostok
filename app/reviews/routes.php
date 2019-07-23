<?php

d()->route(d()->langlink . '/reviews/', function () {
	d()->use_page_model();
	d()->reviews_list = d()->Review->order_by('dt_at desc');
	if (!iam()) {
		d()->reviews_list->only('published');
	}
	d()->reviews_list->paginate(12);
	if ($_GET) {
		d()->canonical = d()->url_path;
	}
	d()->view->render('/reviews/reviews.html');
});

//d()->route('/reviews/:url', function ($url) {
//	d()->this = d()->Review->where('url = ?', $url);
//	if (!d()->this->ne)
//	{
//		d()->page_not_found();
//	}
//	d()->crumbs_list = [d()->page_crumb('/reviews/'), d()->crumb_for(d()->this, false)];
//});