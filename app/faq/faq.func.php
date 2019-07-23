<?php
d()->faq_categories_url_base = d()->langlink . '/faq/';

d()->route(d()->langlink .'/faq', function (){
	d()->use_page_model();
	d()->this_faq = d()->Faq;
	d()->view->render('/faq/faq.html');
});

d()->route(d()->langlink .'/faq/:url', function ($url) {
	#d()->use_page_model('/faq');
	d()->this = d()->this_faq_category = d()->Faq_category->where('url = ?', $url);
	d()->this_faq = d()->Faq->where('faq_category_id = ?', d()->this_faq_category->id);
	d()->crumbs_list = [d()->page_crumb('/faq'), d()->crumb_for(d()->this, false)];
	d()->view->render('/faq/faq.html');
});