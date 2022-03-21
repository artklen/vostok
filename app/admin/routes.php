<?php

d()->route('/admin/order_products/:id', function($id) {
	#var_dump($id);die;
	#d()->this = d()->Order->f($id);
	d()->this = d()->order = d()->Order->where('id = ?', $id);
	d()->orders_items = d()->this->orders_items;
	d()->delivery = d()->Delivery_variant->find_by_id(d()->this->delivery_type);
	d()->delivery_bool = false;
	if (d()->delivery->ne){
		if (d()->delivery->price != "" && d()->delivery->price*1 >0){
			if (d()->delivery->free_price!= "" && d()->delivery->free_price >= d()->this->order_price - d()->delivery->price*1){
				d()->delivery_bool = true;
			}elseif (d()->delivery->free_price==""){
				d()->delivery_bool = true;
			}
		}
	}
	return d()->view->render('/admin/order_products.html');
});
d()->get('/admin/edit/users/add', function() {
    return d()->view->render('/admin/user_add.html');
});

d()->post('/admin/edit/users/add', function() {
	if (d()->validate(d()->url_path)) {
		$user = d()->User->where('`email`=? ', d()->params['email']);
		if (!$user->ne) {
			$user = d()->User->new;
			$user->name = d()->params['name'];
			$user->email = d()->params['email'];
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
d()->post('/admin/edit/orders/:key', function ($key){
    if (d()->validate('admin_save_data')){
       d()->order = d()->Order($key);
       if (d()->order->status_id != d()->params['status_id']){
           $order = d()->Order($key);
           $order->status_id = d()->params['status_id'];
           $order->save();
           $user = d()->User->find_by('id', d()->order->user_id);
           $order = d()->Order($key);
           if (d()->order->user_id != "" && $user->ne) {
               d()->notification->notice_user_order_status_change( $order);
           }
       }
    }
    header('Location: /admin/list/orders');
    exit;
});