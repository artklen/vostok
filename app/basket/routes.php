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
	    'errors' => $basket->errors(),
		'total_number' => $basket->total_number(),
		'total_price' => $basket->total_price(),
		'total_weight' => $basket->total_weight(),
		'products_price' => $basket->products_price(),
		'products_discount' => $basket->products_discount(),
		'order_price' => $basket->order_price(),
		'delivery_price' => d()->price_format($basket->delivery_price()),
		'delivery_working_days' => $basket->delivery_working_days_description(),
		'is_free_delivery' => $basket->is_free_delivery(),
		'items' => [],
		'items_total_price' => array(),
		'items_total_weight' => array(),
		'widgets' => array(),
		'widgets_ids' => array(),
        'delivery_cdek_point_city_title' => $basket->delivery_cdek_point_city_title(),
        'delivery_cdek_point_title' => $basket->delivery_cdek_point_title(),
        'delivery_cdek_courier_city' => $basket->delivery_cdek_courier_city()->to_array(),
        'delivery_cdek_courier_address' => $basket->delivery_cdek_courier_address(),
        'delivery_post_address' => $basket->delivery_post_address(),
        'cash_on_delivery_title' => $basket->cash_on_delivery_title(),
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

	return 'Basket.refresh(' . json_encode($result) . ');';
};

// страница корзины
d()->get(d()->langlink . '/basket', function() {
	d()->set_page_title(t('Корзина'));
	return d()->view->partial('/basket/index.html');
});

// оформление заказа
d()->post(d()->langlink . '/basket/finish', function() {
	if (! AJAX) {
        header('Location: ' . d()->url_path);
        exit;
    }

    $basket = d()->basket;
    if (! isset($basket->order)) {
        print 'document.location.href="' . d()->langlink . '/basket/";';
        exit;
    }

    d()->validate(d()->url_path);
    $basket->validate_delivery();
    if (! d()->validate(d()->url_path)) {
        basket_finish_show_notices();
    }

    $order = $basket->order;
    $order->ordered_at = date('Y-m-d H:i:s', d()->time);
    $order->status_id = Order::CREATED;
    $order->name = d()->params['name'];
    $order->phone = d()->params['phone'];
    $order->address = d()->params['address'];
    $order->email = d()->params['email'];
    $order->comment = d()->params['comment'];
    $order->delivery_type = d()->params['delivery_type'];
    if (d()->Auth->is_authorized){
        $order->user_id = d()->Auth->user->id;
    }
    $order->delivery_price = $basket->calculate_delivery_price();
    $basket->lock_delivery_data();
    $basket->clear_irrelevant_delivery_data();

    $errors = $basket->errors();
    $order->errors = $errors ? json_encode($errors, JSON_UNESCAPED_UNICODE) : '';

    $order->secret = md5(uniqid(mt_rand() . json_encode($_SERVER) . session_id() . microtime(), true));
    $order->save();

    $order = d()->Order->find_by('id', $order->id);
    $order->lock_data();

    $order = d()->Order->find_by('id', $order->id);

    //d()->notification->new_order($order);
	d()->notification->order_created($order);
    if ($order->pay_type === PaymentType::ONLINE) {
        print 'document.location.href="/aquiring/sber/payfororder/' . $order->secret . '";';
    } else {
        print 'document.location.href="' . d()->langlink . '/thankyou"';
    }
    exit;
});

function basket_finish_show_notices() {
    if (isset($_POST['is_modal'])) {
        print 'fancybox_unlock();';
    }
    d()->reload();
}

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

d()->post(d()->langlink . '/basket/set_delivery_type', function () {
    d()->basket->set_delivery_type($_POST);
    print d()->basket_ajax_refresh();
    exit();
});

d()->get(d()->langlink . '/basket/change_cdek_delivery_point', function () {
    print d()->view->render('/basket/modals/change_cdek_delivery_point.html');
    exit;
});

d()->get(d()->langlink . '/basket/load_cdek_delivery_cities', function () {
    $q = $_GET['q'];
    $max = 10;

    $cities = (d()->Cdek_city
        ->search('title', $q)
        ->limit($max)
        ->select('`title`, `subtitle`, `id`, `fias`')
        ->order('`title` not like ' . e($q . '%') . ', `title`')
    );

    $result = array_map(
        static function ($item) {
            return [
                'title' => $item['title'],
                'subtitle' => $item['subtitle'],
                'code' => $item['id'],
                'fias' => $item['fias'],
            ];
        },
        $cities->to_array()
    );

    print json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
});

d()->post(d()->langlink . '/basket/set_delivery_cdek_city', function () {
    d()->basket->set_delivery_cdek_point_city($_POST);
    print d()->basket_ajax_refresh();
    exit();
});

d()->get(d()->langlink . '/basket/load_cdek_delivery_points', function () {
    $points = d()->Cdek->points($_GET['city_code']);
    print json_encode($points, JSON_UNESCAPED_UNICODE);
    exit;
});

d()->post(d()->langlink . '/basket/set_delivery_cdek_point', function () {
    d()->basket->set_delivery_cdek_point($_POST);
    print d()->basket_ajax_refresh();
    exit();
});

d()->get(d()->langlink . '/basket/change_cdek_delivery_courier', function () {
    if (d()->Auth->is_authorized) {
        d()->address = d()->Addres->find_by('user_id', d()->Auth->user->id)->order_by('created_at desc')->limit(10);
    } else {
        d()->address = d()->Addres->where('false');
    }
    print d()->view->render('/basket/modals/change_cdek_delivery_courier.html');
    exit;
});

d()->post(d()->langlink . '/basket/set_delivery_cdek_courier_city', function () {
    d()->basket->set_delivery_cdek_courier_city($_POST);
    print d()->basket_ajax_refresh();
    exit();
});

d()->post(d()->langlink . '/basket/set_delivery_cdek_courier_address', function () {
    d()->basket->set_delivery_cdek_courier_address($_POST);
    print d()->basket_ajax_refresh();
    exit();
});

d()->get(d()->langlink . '/basket/change_post_delivery', function () {
    if (d()->Auth->is_authorized) {
        d()->address = d()->Addres->find_by('user_id', d()->Auth->user->id)->order_by('created_at desc')->limit(10);
    } else {
        d()->address = d()->Addres->where('false');
    }
    print d()->view->render('/basket/modals/change_post_delivery.html');
    exit;
});

d()->post(d()->langlink . '/basket/set_delivery_post_address', function () {
    d()->basket->set_delivery_post_address($_POST);
    print d()->basket_ajax_refresh();
    exit();
});

d()->post(d()->langlink . '/basket/set_pay_type', function () {
    d()->basket->set_pay_type($_POST);
    print d()->basket_ajax_refresh();
    exit();
});
