<?php

d()->route('/faq', function (){
	d()->use_page_model();
	d()->this_faq = d()->Faq;
	d()->view->render('/pages/faq.html');
});

d()->route('/faq/:url', function ($url) {
	d()->this_faq_category = d()->Faq_category->where('url = ?', $url);
	d()->this_faq = d()->Faq->where('faq_category_id = ?', d()->this_faq_category->id);
	d()->view->render('/pages/faq.html');
});