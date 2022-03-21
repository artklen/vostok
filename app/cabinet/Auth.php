<?php

class Auth extends UniversalSingletoneHelper
{

	private $_id;
	
	function is_guest()
	{
		return !isset($_SESSION['auth']);
	}
	
	function is_authorised()
	{
		return isset($_SESSION['auth']);
	}
	
	function login($user_id)
	{
		$user = d()->User->find_by('id', $user_id);
		$_SESSION['auth'] = $user_id;
		$this->_id = $user_id;
	}
	
	function logout()
	{
		unset($_SESSION['auth']);
		unset($this->_id);
	}
	
	function id()
	{
		return $_SESSION['auth'];
	}
	
	function user($id = false)
	{
		if ($id === false){
			$id = $_SESSION['auth'];
		}
		return d()->User($id);
	}

}
