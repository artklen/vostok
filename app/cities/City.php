<?php

class City extends ActiveRecord
{
	
	use DomainsModel;
	
	use ModelsFieldsHelper;
	
	function first_letter()
	{
		return mb_strtoupper(mb_substr($this->title, 0, 1));
	}
	
	function second_title()
	{
		$result = $this->get(__FUNCTION__);
		if ($result === '') {
			$result = 'в ' . $this->title;
		}
		return $result;
	}
	
}
