<?php

d()->get(d()->langlink . '/cabinet/registration', function() {
    d()->set_page_title('Регистрация');
    return d()->view->render('/cabinet/registration.html');
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
