<?php

class User extends ActiveRecord
{
	function secret()
	{
		if($this->get('secret')!=''){
			return $this->get('secret');
		}
	
		if(d()->User->find($this->id)->get('secret')!=''){
			return d()->User->find($this->id)->get('secret');
		}
		$sid = md5(uniqid(rand().json_encode($_SERVER). session_id() ,true));
		$us = d()->User->find($this->id);
		$us->secret = $sid;
		$us->save();
		return $sid;
	}
	
}
