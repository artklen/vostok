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
		d()->order_t = $order;
		if ($order->email !== '') {
			$message = d()->letter->render('order/client');
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($order->email);
			d()->mail->setSubject(t('Новый заказ на сайте'));
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
		$emails = explode(',', d()->Option->feedback_email);
		foreach($emails as $one_email){
			$one_email = trim($one_email);
			$message = d()->letter->render('order/new');
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
		d()->user = d()->User($order->user_id);
		if ($order->email !== '') {
			$message = d()->letter->render('cabinet/notice_user_order_status_change');
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
	function registration($user){
		d()->user = $user;
		if ($user->email !== '') {
			$message = d()->letter->render('cabinet/registration');
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($user->email);
			d()->mail->setSubject(t('Регистрация на сайте'));
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
	}
	function user_restore($user){
		d()->user = $user;
		if ($user->email !== '') {
			$message = d()->letter->render('cabinet/user_restore');
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($user->email);
			d()->mail->setSubject(t('Восстановление пароля на сайте'));
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
	}
	function user_restore_finish($user){
		d()->user = $user;
		if ($user->email !== '') {
			$message = d()->letter->render('cabinet/user_restore_finish');
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($user->email);
			d()->mail->setSubject(t('Смена пароля на сайте'));
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
	}
	function registration_by_admin($user, $password){
		d()->user = $user;
		d()->password = $password;
		if ($user->email !== '') {
			$message = d()->letter->render('cabinet/registration_by_admin');
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($user->email);
			d()->mail->setSubject(t('Создан аккаунт на сайте'));
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
	}

}
