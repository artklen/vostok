<?php

d()->get(d()->langlink . '/cabinet/login', function() {
	d()->set_page_title('Авторизация');
	return d()->view->render('/cabinet/login.html');
});

d()->get(d()->langlink . '/cabinet/registration', function() {
	d()->set_page_title('Регистрация');
	return d()->view->render('/cabinet/registration.html');
});

d()->get(d()->langlink . '/cabinet/profile', function() {
	if (d()->Auth->is_guest) {
		header('Location: /cabinet/login');
		exit;
	}
	d()->user = d()->Auth->user;
	d()->set_page_title('Мой профиль');
	return d()->view->render('/cabinet/profile.html');
});
d()->get(d()->langlink . '/cabinet/address', function() {
	if (d()->Auth->is_guest) {
		header('Location: /cabinet/login');
		exit;
	}
	d()->user = d()->Auth->user;
	d()->address = d()->Addres->find_by('user_id', d()->user->id)->order_by('created_at desc');
	d()->set_page_title('Мой профиль');
	return d()->view->render('/cabinet/address.html');
});

d()->get(d()->langlink . '/cabinet/add_address', function() {
	if (d()->Auth->is_guest) {
		header('Location: /cabinet/login');
		exit;
	}
	d()->user = d()->Auth->user;
	print d()->view->render('/cabinet/add_address.html');
	exit;
});
d()->get(d()->langlink . '/cabinet/edit_address/:key', function($key) {
	if (d()->Auth->is_guest) {
		header('Location: /cabinet/login');
		exit;
	}
	d()->user = d()->Auth->user;
	d()->address = d()->Addres->where('`id`=? and `user_id`=?', $key, d()->Auth->user->id);
	print d()->view->render('/cabinet/edit_address.html');
	exit;
});
d()->get(d()->langlink . '/cabinet/delete_address/:key', function($key) {
	if (d()->Auth->is_guest) {
		header('Location: /cabinet/login');
		exit;
	}
	$address = d()->Addres->where('`id`=? and `user_id`=?', $key, d()->Auth->user->id);
	if ($address->ne){
		$address = d()->Addres($key);
		$address->delete();
	}
	header('Location: /cabinet/address');
	exit;
});

d()->get(d()->langlink . '/cabinet/orders', function() {
	if (d()->Auth->is_guest) {
		header('Location: /cabinet/login');
		exit;
	}
	d()->user = d()->Auth->user;
	d()->orders = d()->Order->where('`user_id`=? and not isnull(`status_id`)', d()->user->id)->order_by('created_at desc');
	return d()->view->render('/cabinet/orders.html');
});
d()->get(d()->langlink . '/cabinet/orders/:key', function($key) {
	d()->order = d()->Order->where('`id`=? and (`user_id`=? or `email`=?)', $key, d()->Auth->user->id, d()->Auth->user->email);
	if (d()->Auth->is_guest || d()->order->is_empty) {
		header('Location: /cabinet/login');
		exit;
	}
	d()->items = d()->Orders_item->find_by('order_id', $key);
	return d()->view->render('/cabinet/order-single-page.html');
});

d()->get(d()->langlink . '/cabinet/', function() {
	if (d()->Auth->is_guest) {
		header('Location: /cabinet/login');
		exit;
	}
	header('Location: /cabinet/profile');
	exit;
});

d()->post(d()->langlink . '/cabinet/add_address', function() {
	if (d()->Auth->is_guest) {
		header('Location: /cabinet/login');
		exit;
	}
	if (d()->validate(d()->url_path)) {
		$user = d()->User->find_by('id', d()->Auth->user->id);
		if ($user->ne) {
			$address = d()->Addres->where('`title`=? and `user_id`=?', d()->params['address'], d()->Auth->user->id);
			if (!$address->ne) {
				$full_address = explode("@", d()->params['full_addr']);
				$address = d()->Addres->new;
				if (isset($full_address[0]) and $full_address[0]!=""){
					$address->post = $full_address[0];
				}
				if (isset($full_address[1]) and $full_address[1]!=""){
					$address->city = $full_address[1];
				}
				if (isset($full_address[2]) and $full_address[2]!=""){
					$address->street = $full_address[2];
				}
				if (isset($full_address[3]) and $full_address[3]!=""){
					$address->cdek_id = $full_address[3];
				}
				$address->dadata = d()->params['dadata'];
				$address->title = d()->params['address'];
				$address->user_id = $user->id;
				$address->save();
			}
		}
	}
	print 'document.location.href = document.location.href;';
	exit();
});
d()->post(d()->langlink . '/cabinet/edit_address/:key', function($key) {
	if (d()->Auth->is_guest) {
		header('Location: /cabinet/login');
		exit;
	}
	if (d()->validate(d()->url_path)) {
		$user = d()->User->find_by('id', d()->Auth->user->id);
		if ($user->ne) {
			$address = d()->Addres->where('`id`=? and `user_id`=?', $key, d()->Auth->user->id);
			if ($address->ne) {
				$address_dub = d()->Addres->where('`title`=? and `user_id`=?', d()->params['address'], d()->Auth->user->id);
				if (!$address_dub->ne){
					$full_address = explode("@", d()->params['full_addr']);
					$address = d()->Addres($key);
					if (isset($full_address[0]) and $full_address[0]!=""){
						$address->post = $full_address[0];
					}
					if (isset($full_address[1]) and $full_address[1]!=""){
						$address->city = $full_address[1];
					}
					if (isset($full_address[2]) and $full_address[2]!=""){
						$address->street = $full_address[2];
					}
					$address->title = d()->params['address'];
					$address->save();
				}
			}
		}
	}
	print 'document.location.href = document.location.href;';
	exit();
});


d()->post(d()->langlink . '/cabinet/logout', function() {
	d()->Auth->logout();
	if (AJAX) {
		print 'document.location.href = "/";';
		exit;
	}
	header('Location: /');
	exit;
});

d()->post(d()->langlink . '/cabinet/login', function() {
	if (d()->validate(d()->url_path)) {
		$user = d()->User->where('`email`=? and `password`=? and `is_active`=1', d()->params['email'], d()->user_password_hash(d()->params['password']));
		if ($user->ne) {
			d()->Auth->login($user->id);
			print 'document.location.href="'. d()->langlink . '/cabinet/";';
			exit;
		}
		d()->add_notice('<div class="field-description error with-bg margin-0-0-8px">Не удается войти!<br>Адрес электронной почты или пароль не верны!</div>');
	}
	if (d()->notice()) {
		print '_current_form.find(".js-notice").html(' .  d()->to_json(d()->notice) .  ');';
		print '_current_form.find("input[name=email],input[name=password]").val("");';
	}
	d()->reload();
	exit;
});

d()->post(d()->langlink . '/cabinet/registration', function() {
	if (d()->validate(d()->url_path)) {
		$user = d()->User->where('`email`=? ', d()->params['email']);
		if (!$user->ne) {
			$user = d()->User->new;
			$user->name = d()->params['name'];
			$user->phone = d()->params['phone'];
			$user->email = d()->params['email'];
			$user->password = d()->user_password_hash(d()->params['password']);
			$user->secret = md5(uniqid(rand() . json_encode($_SERVER). session_id() . microtime() . d()->params['phone'] . d()->params['email'] . d()->user_password_hash(d()->params['password']), true));
			$user->is_active = 0;
			$user->save();
			$user = d()->User->find_by('id', $user->insert_id);
			d()->notification->registration($user);
			//d()->Auth->login($user->id);
			print 'document.location.href = "/thank-you-for-registering";';
			exit();
		}
	}
	if (d()->notice()) {
		print '_current_form.find(".js-notice").html(' .  d()->to_json(d()->notice(['style' => '', 'class' => 'alert alert-danger'])) .  ');';
		
	}
	d()->reload();
	exit;
});
d()->post(d()->langlink . '/cabinet/profile', function() {
	if (d()->validate(d()->url_path)) {
		if (d()->params['email'] != ""){
			$user = d()->User->where('`email`=? ', d()->params['email']);
			if ($user->ne){
				print '_current_form.find(".js-notice").html("Пользователь с такой почтой уже существует");';
				exit;
			}else{
				$user = d()->User->find_by('id', d()->Auth->user->id);
				$user->email = d()->params['email'];
				$user->secret = md5(uniqid(rand() . json_encode($_SERVER). session_id() . microtime() . d()->Auth->user->phone . d()->params['email'] . $user->password, true));
				$user->save();
				$user = d()->User->find_by('id', d()->Auth->user->id);
				d()->notification->registration($user);

			}
		}
		if (d()->params['name'] != ""){
			$user = d()->User->find_by('id', d()->Auth->user->id);
			$user->name = d()->params['name'];
			$user->save();

		}
		if (d()->params['phone'] != ""){
			$user = d()->User->find_by('id', d()->Auth->user->id);
			$user->phone = d()->params['phone'];
			$user->save();

		}
		print 'document.location.href="'.d()->url_path.'";';
		exit;
	}
	if (d()->notice()) {
		print '_current_form.find(".js-notice").html(' .  d()->to_json(d()->notice(['style' => '', 'class' => 'alert alert-danger'])) .  ');';	
	}
	d()->reload();
	exit;
});
d()->get(d()->langlink . '/cabinet/registration/:key', function($key) {
	d()->set_page_title('Введите новый пароль');
	$user = d()->User->find_by('secret', $key);
	if ($user->ne){
		$user->is_active = 1;
		$user->save;
	}
	d()->user = d()->User->find_by('secret', $key);
	return d()->view->render('/cabinet/registration_finish.html');
});

d()->get(d()->langlink . '/cabinet/restore', function() {
	d()->set_page_title('Восстановление пароля');
	return d()->view->render('/cabinet/restore.html');
});

d()->get(d()->langlink . '/cabinet/restore/:key', function($key) {
	d()->set_page_title('Введите новый пароль');
	d()->key = $key;
	d()->user = d()->User->find_by('restore_password_key', $key);
	return d()->view->render('/cabinet/restore_finish.html');
});

d()->post(d()->langlink . '/cabinet/restore', function() {
	if (!AJAX) {
		header('Location: ' . d()->url_path);
		exit;
	}
	if (d()->validate(d()->url_path)) {
		$user = d()->User->find_by('email', d()->params['email']);
		if ($user->ne) {
			$secret = md5(uniqid(rand() . json_encode($_SERVER). session_id() . microtime() . $user->id . $user->password, true));
			$user->restore_password_key = $secret;
			$user->save();
			$user = d()->User->find_by('id', $user->id);
			d()->notification->user_restore($user);
			print '_current_form.find(".js-notice").html("");';
			print '_current_form.find(".error").removeClass("error");';
			print '_current_form.find(".has-error").removeClass("has-error");';
			print '_current_form.find(".js-clear-on-success").html("");';
			print '_current_form.find(".js-success").stop(true, false).fadeOut(150, function() { jQuery(this).css({display: "block", opacity: 1, "border-radius": "5px", border: "solid 2px #4ac670", color:  "#4ac670", background: "#f0f9f2"});jQuery(this).html(\'<ul class="alert alert-success"><li>На указанный адрес электронной почты, если он зарегестрирован на сайте, было отправлено письмо с инструкцией по восстановлению пароля. </li></ul>\'); });';
			exit;
		} else {
			d()->add_notice('Пользователь с такой электронной почтой не зарегистрирован.');
		}
	}
	if (d()->notice()) {
		print '_current_form.find(".js-notice").html(' . json_encode(d()->notice(['style' => '', 'class' => 'alert alert-danger'])) . ');';
	}
	d()->reload();
	exit;
});

d()->post(d()->langlink . '/cabinet/restore_finish', function() {
	if (!AJAX) {
		header('Location: ' . d()->url_path);
		exit;
	}
	if (d()->validate(d()->url_path)) {
		$user = d()->User->find_by('restore_password_key', d()->params['key']);
		if ($user->ne) {
		    if (d()->params['password']!="" && d()->params['password_confirmation']!="" && d()->params['password_confirmation']==d()->params['password']) {
                $user->restore_password_key = '';
                $user->password = d()->user_password_hash(d()->params['password']);
                $user->is_active = 1;
                $user->save();
                //$user = d()->User->find_by('id', $user->id);
                //d()->Auth->login($user->id);
                //d()->notification->user_restore_finish($user);
                print '_current_form.find(".js-notice").html("");';
                print '_current_form.find(".error").removeClass("error");';
                print '_current_form.find(".has-error").removeClass("has-error");';
                print '_current_form.find(".js-clear-on-success").html("");';
                print '_current_form.find(".js-view-success").show();';
                print '_current_form.find(".js-success").stop(true, false).fadeOut(150, function() { jQuery(this).css({display: "block", opacity: 1, "border-radius": "5px", border: "solid 2px #4ac670", color:  "#4ac670", background: "#f0f9f2"});jQuery(this).html(\'<ul class="alert alert-success"><li>Новый паролль успешно установлен.</li></ul>\'); });';
                print 'setTimeout(function() { document.location.href="/cabinet/"; }, 3000);';
                exit;
            } else {
                d()->add_notice("Пароли не совпадают!");
            }
		} else {
			d()->add_notice('Ссылка для восстановления доступа устарела или содержит ошибку.<br>Попробуйте заново <a href="/cabinet/restore">восстановить пароль</a>.');
		}
	}
	if (d()->notice()) {
		print '_current_form.find(".js-notice").html(' . json_encode(d()->notice(['style' => '', 'class' => 'alert alert-danger'])) . ');';
	}
	d()->reload();
	exit;
});