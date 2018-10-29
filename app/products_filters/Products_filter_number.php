<?php

class Products_filter_number extends Products_filter
{
	
	function get_values($object)
	{
		$name = et($this->name);
		$clone = clone $object;
		$clone->select("1 * replace(`{$name}`, ',', '.') as `v`, count(*) as `c`")
			  ->where("`{$name}` is not null and `{$name}`<>''")
			  ->order('`c` desc, `v`')
			  ->group_by("1 * replace(`{$name}`, ',', '.')");
		$result = [];
		foreach ($clone as $row) {
			$result[] = [
				'value' => $row['v'],
				'count' => $row['c'],
			];
		}
		return $result;
	}
	
	function filter($object, $value)
	{
		if (!is_array($value)) {
			$value = [$value];
		}
		if ($value === ['']) {
			return $object;
		}
		return $object->where('1 * replace(`' . et($this->name) . '`, ",", ".") in (?)', $value);
	}
	
}
