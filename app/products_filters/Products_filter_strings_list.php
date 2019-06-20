<?php

class Products_filter_strings_list extends Products_filter
{
	
	function get_values($object)
	{
		$name = et($this->name);
		$clone = clone $object;
		$clone->select("binary `{$name}` as `v`, group_concat(distinct `id`) as `ids`")
			  ->where("`{$name}` is not null and `{$name}`<>''")
			  ->order('`v`')
			  ->group_by("binary `{$name}`");
		$variants = [];
		foreach ($clone as $row) {
			$values = explode("\n", str_replace("\r", '', $row['v']));
			$ids = explode(',', $row['ids']);
			foreach ($values as $value) {
				$value = trim($value);
				if ($value !== '') {
					foreach ($ids as $id) {
						$variants[$value][$id] = true;
					}
				}
			}
		}
		$result = [];
		foreach ($variants as $key => $value) {
			$result[] = [
				'value' => $key,
				'count' => count($value),
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
		$name = et($this->name);
		$conditions = [];
		foreach ($value as $v) {
			$conditions[] = 'find_in_set(' . e($v) . ', replace(replace(binary `' . $name . '`, "\\n", ","), "\\r", ""))';
		}
		return $object->where(implode(' or ', $conditions));
	}
	
}
