<?php

d()->get(d()->langlink . '/cabinet/orders', function() {
    if (d()->Auth->is_guest) {
        header('Location: ' . d()->langlink . '/cabinet/login');
        exit;
    }
    d()->user = d()->Auth->user;
    d()->orders = d()->user->orders();
    return d()->view->render('/cabinet/orders.html');
});

d()->get(d()->langlink . '/cabinet/orders/:key', function($key) {
    d()->order = d()->Order->where('`id`=? and (`user_id`=? or `email`=?)', $key, d()->Auth->user->id, d()->Auth->user->email);
    if (d()->Auth->is_guest || d()->order->is_empty) {
        header('Location: ' . d()->langlink . '/cabinet/login');
        exit;
    }
    d()->items = d()->Orders_item->find_by('order_id', $key);
    return d()->view->render('/cabinet/order-single-page.html');
});
