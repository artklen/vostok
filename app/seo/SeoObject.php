<?php

class SeoObject {
	
	protected $_sources = [];
	
	protected $_data = [];
	
	function __set($name, $value)
	{
		$this->_data[$name] = $value;
	}
	
	function __get($name)
	{
		if (isset($this->_data[$name])) {
			return '' . $this->_data[$name];
		}
		foreach ($this->_sources as $source) {
			if (isset($source[$name]) && "{$source[$name]}" !== '') {
				return "{$source[$name]}";
			}
		}
		return '';
	}
	
	function __isset($name)
	{
		if (isset($this->_data[$name])) {
			return '' . $this->_data[$name] !== '';
		}
		foreach ($this->_sources as $source) {
			if (isset($source[$name]) && "{$source[$name]}" !== '') {
				return true;
			}
		}
		return false;
	}
	
	function __unset($name)
	{
		$this->_data[$name] = '';
	}
	
	function add_source($source, $append = false)
	{
		if ($append) {
			$this->_sources[] = $source;
		} else {
			array_unshift($this->_sources, $source);
		}
	}
	
}
	