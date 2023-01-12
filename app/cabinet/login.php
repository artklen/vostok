<?php

d()->get(d()->langlink . '/cabinet/login', function() {
    d()->set_page_title('Авторизация');
    return d()->view->render('/cabinet/login.html');
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

d()->post(d()->langlink . '/cabinet/logout', function() {
    d()->Auth->logout();
    if (AJAX) {
        print 'document.location.href = "/";';
        exit;
    }
    header('Location: /');
    exit;
});
