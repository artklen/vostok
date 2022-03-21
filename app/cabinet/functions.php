<?php

d()->user_password_hash = function($password) {
	return md5($password);
};
d()->to_json = function($arg, $is_force_object = false) {
	return json_encode($arg, JSON_UNESCAPED_UNICODE | ($is_force_object ? JSON_FORCE_OBJECT : 0));
};
d()->gen_password = function($length = 8)
{				
	$chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP'; 
	$size = strlen($chars) - 1; 
	$password = ''; 
	while($length--) {
		$password .= $chars[random_int(0, $size)]; 
	}
	return $password;
};