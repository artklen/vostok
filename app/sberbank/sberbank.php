<?php
use Voronkovich\SberbankAcquiring\Client;
use Voronkovich\SberbankAcquiring\OrderStatus;
//Эквайринг Сбербанка
//https://github.com/voronkovich/sberbank-acquiring-client

// перенаправляет на страницу оплаты сбербанка
d()->route('/aquiring/sber/payfororder/:order_id', function($order_id) {
	if ($order_id == '') {
		print 'Номер заказа не указан';
		exit;
	}
	$order = d()->Order->find_by('secret', $order_id);
	if ($order->is_empty) {
		print 'Номер заказа не найден';
		exit;
	}
	if ($order->is_paid) {
		//$_SESSION['flash'] = 'Заказ уже оплачен!';
		d()->emit('aquiring.already_payed');
		exit;
	}
	
	try {
		$payment = d()->Payment->new;
		$payment->type      = 'sber';
		$payment->order_id  = $order->id;
		$payment->sum       = $order->order_price;
		$payment->save();
		$payment = d()->Payment->find_by('id', $payment->insert_id);
		
		$client = new Client([
			'userName' => $_ENV['SBER_LOGIN'],
			'password' => $_ENV['SBER_PASSWORD'],
			'apiUri'   => $_ENV['SBER_API_URL'],
			'language' => 'ru',
		]);
		$orderAmount = ceil($order->order_price) . '00';
		$returnUrl = $_ENV['SBER_RETURN_URL'];
		$order_data = [
			'description' => 'Оплата заказа №' . $order->id . ' на сайте ' . $_ENV['SITE_MAIN_DOMAIN'],
		];
		$result = $client->registerOrder($payment->id, $orderAmount, $returnUrl, $order_data);
		
		$payment->sber_url = $result['formUrl'];
		$payment->sber_order_code = $result['orderId'];
		$payment->save();
		header('Location: ' . $result['formUrl']);
	} catch (Exception $e) {
		echo 'Ошибка платежа: ' . $e->getMessage() . "\n";
	}
	exit;
});

d()->route('/aquiring/sber/finish', function() {
	/*
//order_status
0 Заказ зарегистрирован, но не оплачен                                 // отмена оплаты
1 Предавторизованная сумма захолдирована (для двухстадийных платежей)  // не нужно
2 Проведена полная авторизация суммы заказа                            // то что надо
3 Авторизация отменена                                                 // отмена оплаты
4 По транзакции была проведена операция возврата                       // отмена оплаты
5 Инициирована авторизация через ACS банка-эмитента                    // ???
6 Авторизация отклонена                                                // отмена оплаты
	*/
	
	$payment = d()->Payment->find_by('sber_order_code', $_GET['orderId']);
	if ($payment->is_empty) {
		print 'Заказ не найден';
		exit;
	}
	if ($payment->is_paid) {
		d()->emit('aquiring.already_payed');
		exit;
	}
	try {
		//Проверка на статус проплаты заказа
		$client = new Client([
			'userName' => $_ENV['SBER_LOGIN'],
			'password' => $_ENV['SBER_PASSWORD'],
			'apiUri'   => $_ENV['SBER_API_URL'],
			'language' => 'ru',
		]);
		$result = $client->getOrderStatusExtended($_GET['orderId']);
 		$payment->full_status = json_encode($result);
		$payment->save();
		if ($result['orderStatus'] == 2) {
		
			//меняем статус оплаты
			$order = d()->Order->find_by('id', $payment->order_id);
			$order->is_paid          = 1;
			$order->sberbank_code     = $_GET['orderId'];
			$order->orders_payment_id = $payment->id;
			$order->payed_amount      = $result['amount'] / 100.0; //количество в копейках
			$order->save();
			$order = d()->Order->find_by('id', $order->id);
			
			$payment->is_paid = 1;
			$payment->save();

			d()->emit('aquiring.successfull_paid', [$order]);
			
			exit;
		} else {
			if ($result['errorMessage'] == 'Успешно') {
				$error_message = 'Ошибка оплаты: оплата не была совершена. ' . $result['actionCodeDescription'];
			} else {
				$error_message = 'Ошибка оплаты: ' . $result['errorMessage'];
			}
			
			d()->emit('aquiring.error', ['payment'=>$payment, 'error'=>$error_message]);
			exit;
		}
	} catch (Exception $e) {

		d()->emit('aquiring.error', ['payment'=>$payment, 'error'=>$e->getMessage()]);
		
		exit;
	}
});
