<?php

function acquiring_link_for($order) {
	if (is_numeric($order)){
		$order = d()->Order->find_by('id', $order);
	}
	if ($order->is_empty){
		return '#'; //Заказ не найден, ссылка не доступна
	}
	$secret = $order->secret;
	if ($secret === '') {
		$secret = md5(uniqid(rand() . json_encode($_SERVER) . session_id() . microtime() . $order->id, true));
		$order_copy = d()->Order->find_by('id', $order->id);
		$order_copy->secret = $secret;
		$order_copy->save();
	}

    $type = Payment::YOOKASSA_TYPE;
    return '/acquiring/' . $type . '/payfororder/' . $secret;

    //$type = 'sber';
    //return '/aquiring/' . $type . '/payfororder/' . $secret;
}

/*

Карты, возвращающие ошибки /
Cards returning errors:
pan: 5555 5555 5555 5557
exp date: 2019/12
cvv2: 123
3dsecure: veres=y, pares=u
Declined. PaRes status is U (-2011)

Рабочая карта
pan: 4111 1111 1111 1111
exp date: 2019/12
cvv2: 123
3dsecure: veres=y, pares=y

Пароль по СМС ACS: 12345678.
*/