<?php

d()->acquiring_yookassa_order_pay_url_base = '/acquiring/yookassa/payfororder/';
d()->acquiring_yookassa_order_process_url = '/acquiring/yookassa/payfororder/';

d()->route('/acquiring/yookassa/callback', function () {
    $ok = (new YooKassaAcquiring())->callback(file_get_contents('php://input'));
    if (! $ok) {
        header('HTTP/1.1 400 Something went wrong');
    }
    exit;
});

d()->route(d()->acquiring_yookassa_order_pay_url_base . ':secret', function ($secret) {
    $order = (new Order())->find_by('secret', $secret);
    if ($order->is_empty()) {
        header('Location: /');
        exit;
    }

    $purpose = PaymentForOrder::create($order);
    if ($purpose === null) {
        d()->set_page_title('Произошла ошибка');
        return d()->View->render('/pages/show.html');
    }

    if ($purpose->needToPay() && ! $purpose->isPaid()) {
        $redirect = $purpose->pay(new YooKassaAcquiring());
        if ($redirect !== null) {
            header('Location: ' . $redirect);
            exit;
        }

        d()->set_page_title('Произошла ошибка');
        return d()->View->render('/pages/show.html');
    }

    header('Location: /');
    exit;
});

// для обновления статуса оплаты
d()->route(d()->acquiring_yookassa_order_process_url, function() {
    $purpose = (new YooKassaAcquiring())->update($_GET['code'] ?? '');
    if ($purpose === null) {
        d()->set_page_title('Произошла ошибка');
        return d()->View->render('/pages/show.html');
    }

    header('Location: ' . $purpose->statusUrl());
    exit;
});
