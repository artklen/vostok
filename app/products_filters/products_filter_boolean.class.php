<?php

class Products_filter_boolean extends Products_filter
{
	
	function get_values($object, $sort_type = '')
	{
		$name = et($this->name);
		$clone = clone $object;
		$clone->select('count(*) as `c`')
			  ->where("`{$name}` is not null and 1*`{$name}`<>0")
			  ->group_by("1*`{$name}`<>0");
		$result = [];
		foreach ($clone as $row) {
			$result[] = [
				'value' => '1',
				'count' => $row['c'],
			];
		}
		return $result;
	}
	
	function filter($object, $value)
	{
		if (!empty($value)) {
			$name = et($this->name);
			return $object->where("`{$name}` is not null and 1*`{$name}`<>0");
		}
		return $object;
	}
	
}
