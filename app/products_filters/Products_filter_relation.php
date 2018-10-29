<?php

class Products_filter_relation extends Products_filter
{
	
	public $table;
	public $field_from;
	public $field_to;
	
	function __construct($params)
	{
		$this->table      = $params['table'];
		$this->field_from = $params['field_from'];
		$this->field_to   = $params['field_to'];
	}
	
	function get_values($object, $sort_type = '')
	{
		$clone = clone $object;
		$ids_from = $clone->select('`id`')->order('`sort`')->fast_all_of('id');
		
		$field_from   = et($this->field_from);
		$field_to     = et($this->field_to);
		$table_object = activerecord_factory_from_table($this->table);
		$table_object->select("`{$field_to}` as `v`, count(*) as `c`")
			         ->where("`{$field_from}` in (?)", $ids_from)
			         ->order('`c` desc, `v`')
			         ->group_by("`{$field_to}`");
		
		$result = [];
		foreach ($table_object as $row) {
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
		
		$field_from   = et($this->field_from);
		$field_to     = et($this->field_to);
		$table_object = activerecord_factory_from_table($this->table);
		$from_ids     = $table_object->where("`{$field_to}` in (?)", $value)
									 ->select("distinct `{$field_from}`")->order('')->fast_all_of($field_from);
		
		return $object->where('`id` in (?)', $from_ids);
	}
	
}
