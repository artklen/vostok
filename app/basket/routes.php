<?php
$url_base = '/basket/';

// страница корзины
d()->get('/basket/order', function() {
	d()->set_page_title('Оформление заказа');
	return d()->view->partial('/basket/order.html');
});

// вывод данных для перерисовки корзины
d()->basket_ajax_refresh = function() {
	$basket = d()->basket;
	$result = array(
		'total_number' => d()->basket_total_number,
		'total_price' => d()->basket_total_price,
		'total_weight' => d()->basket_total_weight,
		'order_price' => d()->basket_order_price,
		'items' => array(),
		'items_total_price' => array(),
		'items_total_weight' => array(),
		'widgets' => array(),
		'widgets_ids' => array(),
	);
	foreach (d()->params['widgets'] as $widget_id => $widget_type) {
		if (!isset($result['widgets'][$widget_type])) {
			if (is_file(__DIR__ . '/widgets/' . $widget_type . '.html')) {
				$result['widgets'][$widget_type] = '' . d()->view->partial('/basket/widgets/' . $widget_type . '.html');
			} else {
				$result['widgets'][$widget_type] = '';
			}
		}
		$result['widgets_ids'][$widget_type][] = $widget_id;
	}
	$items = $basket->items();
	foreach ($items as $item) {
		$result['items'][$item->item_key] = $item->number;
		$result['items_total_price'][$item->item_key] = d()->basket_total_price($item->item_key);
		$result['items_total_weight'][$item->item_key] = d()->basket_total_weight($item->item_key);
	}
	return 'Basket.refresh(' . json_encode($result) . ');$.fancybox.update();';
};



// страница корзины
d()->get('/basket', function() {
	d()->set_page_title('Корзина');
	return d()->view->partial('/basket/index.html');
});

// оформление заказа
d()->post('/basket/finish', function() {
	if (AJAX) {
		if (!isset(d()->basket->order)) {
			print 'document.location.href="/basket/";';
			exit;
		}
		if (d()->validate(d()->url_path)) {
			$order = d()->basket->order;
			$order->ordered_at   = date('Y-m-d H:i:s', d()->time);
			$order->status_id    = Order::CREATED;
			$order->name         = d()->params['name'];
			$order->phone        = d()->params['phone'];
			$order->address        = d()->params['address'];
			$order->email        = d()->params['email'];
			$order->comment      = d()->params['comment'];
			$order->delivery_type = d()->params['delivery_type'];
			$order->pay_type = d()->params['pay_type'];
			
			
			$order->secret       = md5(uniqid(rand() . json_encode($_SERVER) . session_id() . microtime(), true));
			$order->save();

			$order = d()->Order->find_by('id', $order->id);
			$order->lock_data();
			
			$order = d()->Order->find_by('id', $order->id);
			

			d()->notification->new_order($order);
			if (params['pay_type']==1){
				print 'document.location.href="/aquiring/sber/payfororder/'.$order->secret.'";';
			} else {
				print 'document.location.href="/thankyou"';
			}
			exit;
		}
		if (isset($_POST['is_modal'])) {
			print 'fancybox_unlock();';
		}
 
		print d()->print_ajax_notice();
		print d()->reload();
	}
	header('Location: ' . d()->url_path);
	exit;
});

// API
d()->post('/basket/:method', function($method) {
	if (d()->validate(d()->url_path)) {
		d()->basket->$method(d()->params);
	}
	if (AJAX) {
		if ($method === 'add_item') {
			d()->basket_item = d()->basket->items(d()->params);
			/*print <<<h
var jb = $('.js-basket-add');
jb.html('Товар добавлен в корзину');
jb.attr('href', '/basket');
h;*/
			print 'Basket.popup(' . json_encode('' . d()->view->partial('/basket/add_popup.html')) . ');';
		}
		print d()->basket_ajax_refresh();
		exit;
	} else {
		header('Location: /basket/');
		exit;
	}
});