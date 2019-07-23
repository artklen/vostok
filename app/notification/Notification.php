<?php

class Notification {
	
	function feedback($name)
	{
		$template = "feedback/$name";
		if (!is_file(__DIR__ . "/../letter/$template.html")) {
			$template = 'feedback/default';
		}
		$message = d()->letter->render($template);
		$emails = explode(',', d()->Option->feedback_email);
		foreach ($emails as $email) {
			$email = trim($email);
			if ($email === '') {
				continue;
			}
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($email);
			d()->mail->setSubject(d()->feedback_form[$name]['title']);
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
	}
	
	function order_created($order) {
		$old_context = array(
			'order' => d()->order,
		);
		d()->order = $order;
		if ($order->email !== '') {
			$message = d()->letter->order_created('user');
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($order->email);
			d()->mail->setSubject(t('Новый заказ на сайте'));
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
		
		foreach(explode(',', $email) as $one_email){
			$one_email = trim($one_email);
			$message = d()->letter->feedback_order;
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($one_email);
			d()->mail->setSubject(t('Новый заказ на сайте'));
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
		
		foreach ($old_context as $key => $value) {
			d()->$key = $value;
		}
	}
	
	function new_order($order)
	{
		$old_context = [
			'order' => d()->order,
		];
		d()->order = $order;
		$message = d()->letter->render('order/new');
		$emails = explode(',', d()->Option->feedback_email);
		foreach ($emails as $email) {
			$email = trim($email);
			if ($email === '') {
				continue;
			}
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($email);
			d()->mail->setSubject(t('Новый заказ с сайта'));
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();

		}
		foreach ($old_context as $key => $value) {
			d()->$key = $value;
		}

	}	
	function notice_user_order_status_change($order){
		$old_context = array(
			'order' => d()->order,
		);
		d()->order = $order;
		if ($order->email !== '') {
			$message = d()->letter->orders_status_change('user');
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($order->email);
			d()->mail->setSubject(t('Смена статуса заказа на сайте'));
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
		
		foreach ($old_context as $key => $value) {
			d()->$key = $value;
		}
	}

}
