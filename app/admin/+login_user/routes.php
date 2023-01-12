<?php

d()->get('/admin/login_user/:id', static function ($id) {
    if (! iam_allow('users')){
        return 'Вам запрещён доступ к этому разделу.';
    }

    d()->user = d()->User->f($id);
    if (d()->user->is_empty()) {
        return '';
    }

    return d()->view->render('/admin/+login_user/show.html');
});

d()->post('/admin/login_user/:id', function($id) {
    if (! iam_allow('users')){
        return 'Вам запрещён доступ к этому разделу.';
    }

    d()->Auth->login($id);

    header('Location: /cabinet/');
    exit;
});