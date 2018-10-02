<?php

class Products_filter_interval extends Products_filter
{
	
	public $name;
	
	function __construct($name)
	{
		$this->name = $name;
	}
	
	function get_values($object, $sort_type = '')
	{
		$name = et($this->name);
		$clone = clone $object;
		$clone->select("min(1*`{$name}`) as `min`, max(1*`{$name}`) as `max`")->where("1*`{$name}` > 1e-7")->order('');
		if ($clone->ne) {
			return [
				'min' => $clone['min'],
				'max' => $clone['max'],
			];
		}
		return [
			'min' => '',
			'max' => '',
		];
	}
	
	function filter($object, $value)
	{
		if (!is_array($value)) {
			return $object;
		}
		$name = et($this->name);
		$result = $object;
		if (isset($value['min'])) {
			$object->where("1*`{$name}` - ? > -1e-7", $value['min']);
		}
		if (isset($value['max'])) {
			$object->where("1*`{$name}` - ? < 1e-7", $value['max']);
		}
		return $object;
	}
	
}
