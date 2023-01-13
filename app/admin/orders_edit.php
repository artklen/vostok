<?php

d()->post('/admin/edit/orders/:key', function ($id){
    if (d()->validate('admin_save_data')){
        d()->order = d()->Order->f($id);
        if (d()->order->status_id != d()->params['status_id']){
            $order = d()->Order->f($id);
            $order->status_id = d()->params['status_id'];
            $order->track = d()->params['track'];
            $order->save();
            $user = d()->User->find_by('id', d()->order->user_id);
            $order = d()->Order($id);
            if (d()->order->user_id != "" && $user->ne) {
                d()->notification->notice_user_order_status_change( $order);
            }
        } else {
            $order = d()->Order->f($id);
            $order->track = d()->params['track'];
            $order->save();
        }
    }
    header('Location: /admin/list/orders');
    exit;
});
