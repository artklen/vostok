<?php

d()->route('/product/:url', function($url) {
	d()->this = d()->Product->find_by('url', $url);
	d()->view->render('/product/product.html');
});

d()->route('/product', function($url) {
	d()->page_not_found();
});