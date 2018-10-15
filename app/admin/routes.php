<?php

d()->route('/admin/order_products/:id', function($id) {
	#var_dump($id);die;
	#d()->this = d()->Order->f($id);
	d()->this = d()->Order->where('id = ?', $id);
	d()->orders_items = d()->this->orders_items;
	return d()->view->render('/admin/order_products.html');
});