<?php
d()->on('aquiring.already_payed',function(){
	print "Уже оплачено!";
	exit;
});

d()->on('aquiring.error',function($x){
	print "Ошибка оплаты!";
	var_dump($x);
	exit;
});

/** @see https://www.chekonline.ru/docs/cloudapi_complex.pdf */
d()->on('aquiring.successfull_paid',function($param){
    $testMode = iam('developer') && d()->url_path === '/test_payment';

	$order = $param[0];
	if($order->is_paid){
		d()->order_t = d()->Order->find_by_id($order->id);

        if ($testMode) {
            $emails = ['keeper.ak@gmail.com'];
        } else {
            $emails = explode(',', d()->Option->feedback_email);
        }

		foreach ($emails as $email){
			$message = d()->letter->render('order/manager');
			$email = trim($email);		
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME2']));
			d()->mail->setTo($email);
			d()->mail->setSubject('Получена оплата по заказу');
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
		if (d()->order_t->email != ""){
			$message = d()->letter->render('order/client');
			$email = trim(d()->order_t->email);		
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME2']));
			d()->mail->setTo($email);
			d()->mail->setSubject('Оплата заказа на сайте '.$_SERVER['HTTP_HOST']);
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
		//=====================================================
        if (time() >= strtotime('2022-04-05 2:00')) {
            $url = "https://kkt4.chekonline.ru/fr/api/v2/Complex";
        } else {
            $url = "https://kkt.chekonline.ru/fr/api/v2/Complex";
        }
        if (time() >= strtotime('2026-01-01 0:00')) {
            $TaxId = 7;
        } else {
            $TaxId = 4;
        }
        //$url = "https://kkt.chekonline.ru/fr/api/v2/Complex";
		$datas = array(
			"Device" => "auto",
			'RequestId' => uniqid(),
			"Lines" => array(),
			"NonCash" => array(round(d()->order_t->payed_amount*100)),
			"TaxMode" => 0,
			"PhoneOrEmail" => (d()->order_t->email != "")?d()->order_t->email:d()->order_t->phone,
            "Internet" => true,
            "Timezone" => 2,
		);
        /** @var Orders_item $order_item */
        foreach (d()->order_t->orders_items->all as $order_item){
            $price = (float) $order_item->price_with_discount();
            if ($price < 1e-7) {
                continue;
            }

			$datas['Lines'][]=array(
				"Qty" => $order_item->number * 1000,
				"Price" => $order_item->price_with_discount() * 100,
				"PayAttribute" => 1,
                "LineAttribute" => 1,
				"TaxId" => $TaxId,
				"Description"=> $order_item->title,
			);
		}
		
		//$delivery = d()->Delivery_variant->find_by_id(d()->order_t->delivery_type);
		//if ($delivery->ne){
		//	if ($delivery->price != "" && $delivery->price*1 >0){
		//		if ($delivery->free_price!= "" && $delivery->free_price >= d()->order_t->order_price - $delivery->price*1){
		//			$datas['Lines'][]=array(
		//				"Qty" => 1000,
		//				"Price" => $delivery->price * 100,
		//				"PayAttribute" => 1,
		//				"TaxId" => $TaxId,
		//				"Description"=> "Доставка " . $delivery->title,
		//			);
		//		}elseif ($delivery->free_price==""){
		//			$datas['Lines'][]=array(
		//				"Qty" => 1000,
		//				"Price" => $delivery->price * 100,
		//				"PayAttribute" => 1,
		//				"TaxId" => $TaxId,
		//				"Description"=> "Доставка " . $delivery->title,
		//			);
		//		}
		//	}
		//}
		
		if (1. * d()->order_t->delivery_price > 0) {
			$datas['Lines'][]=array(
				"Qty" => 1000,
				"Price" => ceil(d()->order_t->delivery_price * 100.),
				"PayAttribute" => 1,
                "LineAttribute" => 4,
				"TaxId" => $TaxId,
				"Description"=> "Доставка",
			);
		}
		
		d()->order_ch = d()->Order->find_by_id($order->id);
		$mydatas = json_encode($datas);
		d()->order_ch->chek_data = $mydatas;
		var_Export($mydatas);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER,
		array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $mydatas);
		curl_setopt($curl, CURLOPT_URL,$url);

        if ($testMode) {
            // Сертификат
            curl_setopt($curl,CURLOPT_SSLCERT, getcwd().'/app/aquiring/test/certificate.pem');
            // Закрытый ключ
            curl_setopt($curl,CURLOPT_SSLKEY, getcwd().'/app/aquiring/test/privateKey.pem');
        } else {
            // Сертификат
            curl_setopt($curl, CURLOPT_SSLCERT, getcwd() . '/app/aquiring/certificate.pem');
            // Закрытый ключ
            curl_setopt($curl, CURLOPT_SSLKEY, getcwd() . '/app/aquiring/privateKey.pem');
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$json_response = curl_exec($curl);
		d()->order_ch->chek_response = $json_response;
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		d()->order_ch->chek_http_code = $status;
		$lastError = curl_error($curl);
		d()->order_ch->chek_error = $lastError;
		d()->order_ch->save();
		curl_close($curl);
		$response = json_decode($json_response);
		$respons_err = $response->Response;
		if ($respons_err->Error != 0 || $status!=200){
			foreach ($emails as $email){
				$message = d()->letter->render('order/chek_err');
				$email = trim($email);		
				d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME2']));
				d()->mail->setTo($email);
				d()->mail->setSubject('Ошибка генерации чека по заказу');
				d()->mail->setBody($message, 'text/html');
				d()->mail->send();
			}
		}
		//=================================
		//header('Location: /thankyou');
		//exit;
	}	
	//exit;
});
