<?php

// страница корзины
d()->get(d()->langlink . '/basket/order', function() {
	d()->set_page_title(t('Оформление заказа'));
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
		'items' => [],
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
//	$items = $basket->items();
//	foreach ($items as $item) {
//		$result['items'][$item->item_key] = $item->number;
//		$result['items_total_price'][$item->item_key] = d()->basket_total_price($item->item_key);
//		$result['items_total_weight'][$item->item_key] = d()->basket_total_weight($item->item_key);
//	}

    foreach ($basket->items() as $item) {
        $test = $item->product->url;

        $result['items'][] = [
            'id' => $item->product->id,
            'variant_id' => $item->products_variant->is_empty ? 0 : $item->products_variant->id,
            'basket_item_id' => $item->item_key,
            'title' => $item->product->title,
            'img_link' => preview($item->product->image, '33', '33'),
            'price' => d()->price_format($item->product->price),
            'count' => $item->number,
            'link' => $item->product->url,
        ];
    }

	return 'Basket.refresh(' . json_encode($result) . ');$.fancybox.update();';
};

// страница корзины
d()->get(d()->langlink . '/basket', function() {
	d()->set_page_title(t('Корзина'));
	return d()->view->partial('/basket/index.html');
});

// оформление заказа
d()->post(d()->langlink . '/basket/finish', function() {
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
			if (d()->params['pay_type']==1){
				print 'document.location.href="/aquiring/sber/payfororder/'.$order->secret.'";';
			} else {
				unset($_SESSION['delivery_id']);
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

//// API
//d()->post('/basket/:method', function($method) {
//	if (d()->validate(d()->url_path)) {
////		d()->basket->$method(d()->params);
//	}
//
//	if (! AJAX) {
//		header('Location: /basket/');
//		exit;
//	}
//
//	switch ($method) {
////        case 'add_item':
////            $params = array_merge(['products_variant_id' => 0], d()->params);
////            d()->basket->add_item($params);
////            d()->basket_item = d()->find_item($params);
////
////            print 'Basket.popup(' . json_encode('' . d()->view->partial('/basket/add_popup.html')) . ');';
////            break;
//    }
//
//    exit(d()->basket_ajax_refresh());
//});

d()->post(d()->langlink . '/basket/update_item', function () {
    if (! isset($_POST['product_id']) || empty((string) $_POST['product_id'])) {
        exit(json_encode(['ERROR' => 'EMPTY_PRODUCT_ID']));
    }

    $product = d()->Product->find($_POST['product_id'])->first;

    if ($product->is_empty) {
        exit(json_encode(['ERROR' => 'PRODUCT_NOT_FOUND']));
    }

    if (! isset($_POST['products_variant_id']) || empty((string) $_POST['products_variant_id'])) {
        $product_variant = null;
    } else {
        $product_variant = d()->Products_variant->find($_POST['products_variant_id'])->first;

        if ($product->is_empty) {
            exit(json_encode(['ERROR' => 'PRODUCT_VARIANT_NOT_FOUND']));
        }
    }

    $count = intval($_POST['number']);

    //

    $params = [
        'product_id' => $product->id,
        'products_variant_id' => is_null($product_variant) ? 0 : $product_variant->id,
    ];

    d()->basket->update_item(array_merge($_POST, $params), $count);

    //

    print d()->basket_ajax_refresh();
    exit();
});

d()->post(d()->langlink . '/basket/delete_item', function () {
    if (! isset($_POST['product_id']) || empty((string) $_POST['product_id'])) {
        exit(json_encode(['ERROR' => 'EMPTY_PRODUCT_ID']));
    }

    $product = d()->Product->find($_POST['product_id'])->first;

    if ($product->is_empty) {
        exit(json_encode(['ERROR' => 'PRODUCT_NOT_FOUND']));
    }

    if (! isset($_POST['products_variant_id']) || empty((string) $_POST['products_variant_id'])) {
        $product_variant = null;
    } else {
        $product_variant = d()->Products_variant->find($_POST['products_variant_id'])->first;

        if ($product->is_empty) {
            exit(json_encode(['ERROR' => 'PRODUCT_VARIANT_NOT_FOUND']));
        }
    }

    //

    $params = [
        'product_id' => $product->id,
        'products_variant_id' => is_null($product_variant) ? 0 : $product_variant->id,
    ];

    d()->basket->delete_item(array_merge($_POST, $params));

    //

    print d()->basket_ajax_refresh();
    exit();
});

d()->post(d()->langlink . '/basket/add_item', function () {
    if (! isset($_POST['product_id']) || empty((string) $_POST['product_id'])) {
        exit(json_encode(['ERROR' => 'EMPTY_PRODUCT_ID']));
    }

    $product = d()->Product->find($_POST['product_id'])->first;

    if ($product->is_empty) {
        exit(json_encode(['ERROR' => 'PRODUCT_NOT_FOUND']));
    }

    if (! isset($_POST['products_variant_id']) || empty((string) $_POST['products_variant_id'])) {
        $product_variant = null;
    } else {
        $product_variant = d()->Products_variant->find($_POST['products_variant_id'])->first;

        if ($product->is_empty) {
            exit(json_encode(['ERROR' => 'PRODUCT_VARIANT_NOT_FOUND']));
        }
    }

    $count = intval($_POST['number']);

    //

    $params = [
        'product_id' => $product->id,
        'products_variant_id' => is_null($product_variant) ? 0 : $product_variant->id,
    ];

    d()->basket->add_item(array_merge($_POST, $params), $count);
    d()->basket_item = d()->basket->find_item($params);

    //

    print d()->basket_ajax_refresh();
    print 'Basket.popup(' . json_encode('' . d()->view->partial('/basket/add_popup.html')) . ');';
    exit();
});

d()->post(d()->langlink . '/basket/delivery', function () {
    $delivery_id = intval($_POST['delivery_id']);

    //

    $params = [
        'delivery_id' => $delivery_id,
    ];

    d()->basket->delivery(array_merge($_POST, $params));

    //

    exit();
});