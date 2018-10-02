<?php

d()->route('/news', function ($url) {
	d()->use_page_model();
	d()->this_news = d()->News->sort_by('dt_at', 'desc');
	d()->view->render('/pages/news-1.html');
});

d()->route('/news/:url', function ($url) {
	d()->this = d()->News->where('url = ?', $url);
	d()->view->render('/pages/news-2.html');
});

d()->route('/rubrics/:url', function ($url) {
	d()->this_rubric = d()->Rubric->where('url = ?', $url);
	d()->this_news = d()->News->where('rubric_id = ?', d()->this_rubric->id);
	d()->view->render('/pages/news-1.html');
});