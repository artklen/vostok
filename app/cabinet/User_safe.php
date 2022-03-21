<?php

class User_safe extends ActiveRecord {
	
	function save() {
		if (isset($_POST['password'], $_POST['password']['password']) && $_POST['password']['password'] !== '') {
			$password = $_POST['password']['password'];
			unset($_POST['password']['password']);
		}
		$result = parent::save();
		if (isset($password)) {
			$id = $this->insert_id ? $this->insert_id : $this->id;
			d()->db->exec('update `users` set `password`=' . e(d()->user_password_hash($password)) . ' where `id`=' . e($id));
		}
		return $result;
	}
	
}
