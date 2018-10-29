<?php

class Products_filter
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
		$clone->select("binary `{$name}` as `v`, count(*) as `c`")
			  ->where("`{$name}` is not null and `{$name}`<>''")
			  ->order('`c` desc, `v`')
			  ->group_by("binary `{$name}`");
		$result = [];
		foreach ($clone as $row) {
			$result[] = [
				'value' => $row['v'],
				'count' => $row['c'],
			];
		}
		if ($sort_type === 'natural') {
			usort($result, function($a, $b) {
				return strnatcasecmp($a['value'], $b['value']);
			});
		} else if ($sort_type === 'number') {
			usort($result, function($a, $b) {
				$av = 1 * $a['value'];
				$bv = 1 * $b['value'];
				if (abs($av - $bv) < 1e-7) {
					return 0;
				}
				return ($av < $bv) ? -1 : 1;
			});
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
		return $object->where('binary `' . et($this->name) . '` in (?)', $value);
	}
	
}
