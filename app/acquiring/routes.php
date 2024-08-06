<?php

d()->route('/orders/payment-status', static function() {
    if (! isset($_GET['id'])) {
        d()->set_page_title('Заказ не найден');
        return d()->View->render('/pages/show.html');
    }

    $order = (new Order())->find_by('secret', $_GET['id']);
    if ($order->is_empty()) {
        d()->set_page_title('Заказ не найден');
        return d()->View->render('/pages/show.html');
    }

    $purpose = PaymentForOrder::create($order);
    if ($purpose === null) {
        d()->set_page_title('Произошла ошибка');
        return d()->View->render('/pages/show.html');
    }

    $titles = [
        PaymentPurposeStatus::None => 'не оплачен',
        PaymentPurposeStatus::Paid => 'оплачен',
        PaymentPurposeStatus::Free => 'не требует оплаты',
        PaymentPurposeStatus::Pending => 'ожидает оплаты',
        PaymentPurposeStatus::Canceled => 'отменён',
    ];
    d()->set_page_title('Статус оплаты заказа');
    d()->seo->text = <<<TEXT
<p>Заказ №{$order->id}</p>
<p>Статус: {$titles[$purpose->status()]}</p>
TEXT;
    return d()->View->render('/pages/show.html');
});
