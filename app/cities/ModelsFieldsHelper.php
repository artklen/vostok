<?php

trait ModelsFieldsHelper
{
	
	function get_own_or_options_value($name)
	{
		$result = $this->get($name);
		if ($result === '') {
			$result = d()->Option->$name;
		}
		return $result;
	}
	
	function get_own_or_options_value_code($name)
	{
		if ($this->get($name) !== '') {
			return $this->get("{$name}_code");
		}
		if (d()->Option->$name !== '') {
			return d()->Option->{"{$name}_code"};
		}
		return '';
	}
	
	function get_own_or_another_field_value($name, $field)
	{
		$result = $this->get($name);
		if ($result === '') {
			$result = $this->$field;
		}
		return $result;
	}
	
}
