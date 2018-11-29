<?php

d()->route('/news', function () {
	d()->use_page_model();
	d()->this_news = d()->News->order_by('dt_at desc');
	d()->view->render('/news/news-1.html');
});

d()->route('/news/:url', function ($url) {
	d()->this = d()->News->where('url = ?', $url);

	if (!d()->this->ne)
	{
		d()->page_not_found();
	}

	d()->crumbs_list = [d()->page_crumb('/news'), d()->crumb_for(d()->this, false)];
	d()->view->render('/news/news-2.html');
});

d()->route('/rubrics/:url', function ($url) {
	d()->this = d()->this_rubric = d()->Rubric->where('url = ?', $url);

	if (!d()->this->ne)
	{
		d()->page_not_found();
	}

	d()->this_news = d()->News->where('rubric_id = ?', d()->this_rubric->id);
	d()->crumbs_list = [d()->page_crumb('/news'), d()->crumb_for(d()->this, false)];
	d()->view->render('/news/news-1.html');
});