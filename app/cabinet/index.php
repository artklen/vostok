<?php

d()->get(d()->langlink . '/cabinet/', function() {
    if (d()->Auth->is_guest) {
        header('Location: ' . d()->langlink . '/cabinet/login');
        exit;
    }

    d()->user = d()->Auth->user;
    d()->address = d()->user->addresses();
    d()->orders = d()->user->orders();

    d()->set_page_title('Личный кабинет');
	return d()->view->render('/cabinet/index.html');
});

d()->post(d()->langlink . '/cabinet/profile', function() {
    if (d()->Auth->is_guest()) {
        print 'document.location.href="' . d()->langlink . '/cabinet/login";';
        exit;
    }

    $user = d()->Auth->user();
    if ($user->is_empty()){
        d()->Auth->logout();
        print 'document.location.href="' . d()->langlink . '/cabinet/login";';
        exit;
    }

	if (! d()->validate(d()->url_path)) {
        d()->return_ajax_notice();
    }

    $is_email_changed = false;
    $is_any_changed = false;

    if (d()->params['email'] !== '') {
        $with_same_email = d()->User->where('`email`=? and `id`<>?', d()->params['email'], $user->id);
        if ($with_same_email->ne()) {
            d()->add_notice('Пользователь с такой электронной почтой зарегистрирован.');
            d()->return_ajax_notice();
        }

        $user->email = d()->params['email'];
        $user->secret = md5(
            uniqid(
                mt_rand() . json_encode($_SERVER) . session_id() . microtime() . d()->params['email'] . $user->password,
                true
            )
        );

        $is_email_changed = true;
        $is_any_changed = true;
    }
    if (d()->params['name'] !== '') {
        $user->name = d()->params['name'];
        $is_any_changed = true;
    }
    if (d()->params['phone'] !== '') {
        $user->phone = d()->params['phone'];
        $is_any_changed = true;
    }

    if ($is_email_changed) {
        $user = $user->save_and_load();
        d()->notification->registration($user);
    } elseif ($is_any_changed) {
        $user->save();
    }

    print 'if (_current_form[0].disableForm !== undefined) { _current_form[0].disableForm(); }';
    d()->return_ajax_notice();
});
