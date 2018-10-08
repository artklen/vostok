<?php

d()->singleton('basket', function() {
	return d()->Basket->current;
});

d()->basket_key_of = function($params) {
	return d()->basket->item_key_of($params);
};

d()->basket_number_of = function($params) {
	$item = d()->basket->find_item($params);
	return $item->ne ? $item->number: 0;
};

d()->if_basket_empty = function($arg = 'style="display:none"') {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->basket->items->ne ? $arg : '';
};

d()->if_basket_not_empty = function($arg = 'style="display:none"') {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->basket->items()->is_empty ? $arg : '';
};

//d()->if_basket_product_exists = function($product_id, $arg = 'style="display:none"') {
//	if (is_array($product_id)) {
//		$args = $product_id;
//		$product_id = array_shift($args);
//		if (!empty($args)) {
//			$arg = array_shift($arg);
//		}
//	}
//	$item = d()->basket->find_item(['product_id' => $product_id]);
//	return $item->is_empty || !$item->number ? $arg : '';
//};
//
//d()->if_basket_product_not_exists = function($product_id, $arg = 'style="display:none"') {
//	if (is_array($product_id)) {
//		$args = $product_id;
//		$product_id = array_shift($args);
//		if (!empty($args)) {
//			$arg = array_shift($arg);
//		}
//	}
//	$item = d()->basket->find_item(['product_id' => $product_id]);
//	return $item->ne && $item->number ? $arg : '';
//};

d()->if_basket_item_exists = function($item_key, $arg = 'style="display:none"') {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->basket->items($item_key)->is_empty ? $arg : '';
};

d()->if_basket_item_not_exists = function($item_key, $arg = 'style="display:none"') {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->basket->items($item_key)->ne ? $arg : '';
};

d()->basket_total_number = function($arg = null) {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->basket->total_number($arg);
};

d()->basket_total_price = function($arg = null) {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->price_format(d()->basket->total_price($arg));
};

d()->basket_total_weight = function($arg = null) {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->weight_format(d()->basket->total_weight($arg));
};

d()->basket_product_total_number = function($arg = null) {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->basket->product_total_number($arg);
};

d()->basket_product_total_price = function($arg = null) {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->price_format(d()->basket->product_total_price($arg));
};

d()->basket_product_total_weight = function($arg = null) {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->weight_format(d()->basket->product_total_weight($arg));
};

d()->basket_delivery_price = function() {
	return d()->price_format(d()->basket->delivery_price);
};

d()->basket_order_price = function() {
	return d()->price_format(d()->basket->order_price);
};
