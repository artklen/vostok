<?php

class User extends ActiveRecord {

	function title()
	{
		$parts = array();
		if (trim($this->name) !== '') {
			$parts[] = $this->name;
		}
		if (trim($this->email) !== '') {
			$parts[] = $this->email;
		}
		return implode(' — ', $parts);
	}
	
}