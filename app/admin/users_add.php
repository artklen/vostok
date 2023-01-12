<?php

d()->get('/admin/edit/users/add', function() {
    return d()->view->render('/admin/users_add.html');
});

d()->post('/admin/edit/users/add', function() {
    if (d()->validate(d()->url_path)) {
        $user = d()->User->where('`email`=? ', d()->params['email']);
        if (!$user->ne) {
            $user = d()->User->new;
            $user->name = d()->params['name'];
            $user->email = d()->params['email'];
            $user->phone = d()->params['phone'];
            $password = d()->gen_password;
            $user->password = d()->user_password_hash($password);
            $user->secret = md5(uniqid(rand() . json_encode($_SERVER). session_id() . microtime() . d()->params['phone'] . d()->params['email'] . d()->user_password_hash($password), true));
            $user->is_active = 1;
            $user->save();
            $user = d()->User->find_by('id', $user->insert_id);
            d()->notification->registration_by_admin($user,$password);
            //d()->Auth->login($user->id);
            print 'document.location.href = "/admin/list/users";';
            exit();
        }
    }
    if (d()->notice()) {
        print '_current_form.find(".js-notice").html(\'' .  d()->notice() .  '\');';
    }
    d()->reload();
    exit;
});
