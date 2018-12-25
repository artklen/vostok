<?php

d()->route('/admin/order_products/:id', function($id) {
	#var_dump($id);die;
	#d()->this = d()->Order->f($id);
	d()->this = d()->Order->where('id = ?', $id);
	d()->orders_items = d()->this->orders_items;
	d()->delivery = d()->Delivery_variant->find_by_id(d()->this->delivery_type);
	d()->delivery_bool = false;
	if (d()->delivery->ne){
		if (d()->delivery->price != "" && d()->delivery->price*1 >0){
			if (d()->delivery->free_price!= "" && d()->delivery->free_price >= d()->this->order_price - d()->delivery->price*1){
				d()->delivery_bool = true;
			}elseif (d()->delivery->free_price==""){
				d()->delivery_bool = true;
			}
		}
	}
	return d()->view->render('/admin/order_products.html');
});