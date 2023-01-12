<?php

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
                print '_current_form.find(".js-success").stop(true, false).fadeOut(150, function() { jQuery(this).css({display: "block", opacity: 1, "border-radius": "5px", border: "solid 2px #4ac670", color:  "#4ac670", background: "#f0f9f2"});jQuery(this).html(\'<ul class="alert alert-success"><li>Новый пароль успешно установлен.</li></ul>\'); });';
                print 'setTimeout(function() { document.location.href="' . d()->langlink . '/cabinet/"; }, 3000);';
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
