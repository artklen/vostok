<?php

d()->get(d()->langlink . '/cabinet/add_address', function() {
    if (d()->Auth->is_guest) {
        header('Location: ' . d()->langlink . '/cabinet/login');
        exit;
    }
    d()->user = d()->Auth->user;
    print d()->view->render('/cabinet/edit_address.html');
    exit;
});

d()->post(d()->langlink . '/cabinet/add_address', function() {
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


    $duplicate = d()->Addres->where('`title`=? and `user_id`=? and `id`<>?', d()->params['address'], $user->id, $id);
    if ($duplicate->ne()) {
        d()->add_notice('Этот адрес уже есть в списке.');
        d()->return_ajax_notice();
    }

    $address = d()->Addres->new;
    $address->user_id = $user->id;
    fill_address_from_params($address, d()->params);
    $address->save();

    print '$.fancybox.close();';
    refresh_addresses_widget();
    exit();
});


d()->get(d()->langlink . '/cabinet/edit_address/:id', function($id) {
    if (d()->Auth->is_guest) {
        header('Location: ' . d()->langlink . '/cabinet/login');
        exit;
    }
    d()->user = d()->Auth->user;
    d()->address = d()->Addres->where('`id`=? and `user_id`=?', $id, d()->user->id);
    print d()->view->render('/cabinet/edit_address.html');
    exit;
});

d()->post(d()->langlink . '/cabinet/edit_address/:id', function($id) {
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

    $address = d()->Addres->where('`id`=? and `user_id`=?', $id, $user->id);
    if ($address->is_empty()) {
        d()->add_notice('Произошла ошибка. Адрес не найден.');
        d()->return_ajax_notice();
    }

    $duplicate = d()->Addres->where('`title`=? and `user_id`=? and `id`<>?', d()->params['address'], $user->id, $id);
    if ($duplicate->ne()) {
        d()->add_notice('Этот адрес уже есть в списке.');
        d()->return_ajax_notice();
    }

    fill_address_from_params($address, d()->params);
    $address->save();

    print '$.fancybox.close();';
    refresh_addresses_widget();
    exit();
});


d()->get(d()->langlink . '/cabinet/delete_address/:id', function($id) {
    if (d()->Auth->is_guest) {
        header('Location: ' . d()->langlink . '/cabinet/login');
        exit;
    }
    d()->user = d()->Auth->user;
    d()->address = d()->Addres->where('`id`=? and `user_id`=?', $id, d()->user->id);
    print d()->view->render('/cabinet/delete_address.html');
    exit;
});

d()->post(d()->langlink . '/cabinet/delete_address/:id', function($id) {
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

	$address = d()->Addres->where('`id`=? and `user_id`=?', $id, $user->id);
	if ($address->ne()) {
		$address->delete();
	}

    print '$.fancybox.close();';
    refresh_addresses_widget();
    exit();
});


function fill_address_from_params(Addres $address, array $params)
{
    $parts = explode('@', $params['full_addr'] ?? '');
    $address->post = $parts[0] ?? '';
    $address->city = $parts[1] ?? '';
    $address->street = $parts[2] ?? '';
    $address->cdek_id = $parts[3] ?? '';

    $address->title = $params['address'] ?? '';
    $address->dadata = $params['dadata'] ?? '';
}

function refresh_addresses_widget()
{
    d()->address = d()->Auth->user->addresses();
    print '$(".js-addresses-container").html(' . json_encode(d()->View->partial('/cabinet/_addresses.html')) . ');';
}
