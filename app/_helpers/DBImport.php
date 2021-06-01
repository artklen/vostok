<?php

class DBImport {
	
	public static $keys_delimeter = "~";
	
	public $table;
	public $data;
	public $indexes;
	public $update_data;
	public $insert_data;
	public $insert_indexes;
	public $insert_fields_set;
	public $touched_ids_set;
	
	static function read_all($arg) {
		$is_arg_query = preg_match('/\\s/u', $arg);
		$class = get_called_class();
		$obj = new $class($is_arg_query ? null : $arg);
		$obj->query($is_arg_query ? $arg : null);
		$result = $obj->data;
		unset($obj);
		return $result;
	}
	
	public function __construct($table = null) {
		$this->table = $table;
		$this->clear();
	}
	
	public function clear() {
		$this->data              = [];
		$this->indexes           = [];
		$this->update_data       = [];
		$this->insert_data       = [];
		$this->insert_indexes    = [];
		$this->insert_fields_set = [];
		$this->touched_ids_set   = [];
	}
	
	public function query($query = null) {
		if (!isset($query)) {
			$query = 'select * from `' . et($this->table) . '`';
		}
		$stmt = d()->db->query($query);
		while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
			$this->data[$row['id']] = $row;
		}
	}
	
	public function add_key($name, $is_multiple = false) {
		$index = [];
		if ($is_multiple) {
			foreach ($this->data as $id => $row) {
				$index["$row[$name]"][$id] = $id;
			}
		} else {
			foreach ($this->data as $id => $row) {
				$index["$row[$name]"] = $id;
			}
		}
		$this->indexes[$name] = $index;
	}
	
	public function add_complex_key($name, $fields_list, $is_multiple = false) {
		$index = [];
		if ($is_multiple) {
			foreach ($this->data as $id => $row) {
				$key = '';
				foreach ($fields_list as $field) {
					$key .= static::$keys_delimeter . $row[$field];
				}
				$index[$key][$id] = $id;
			}
		} else {
			foreach ($this->data as $id => $row) {
				$key = '';
				foreach ($fields_list as $field) {
					$key .= static::$keys_delimeter . $row[$field];
				}
				$index[$key] = $id;
			}
		}
		$this->indexes[$name] = $index;
	}
	
	public function insert_row($index_name, $index_value, $row) {
		if (is_array($index_value)) {
			$index_value = static::$keys_delimeter . implode(static::$keys_delimeter, $index_value);
		}
		if (!isset($this->insert_indexes[$index_name], $this->insert_indexes[$index_name][$index_value])) {
			$this->insert_data[] = $row;
			end($this->insert_data);
			$this->insert_indexes[$index_name][$index_value] = key($this->insert_data);
		} else {
			$i = $this->insert_indexes[$index_name][$index_value];
			$this->insert_data[$i] = $row + $this->insert_data[$i];
		}
		$this->insert_fields_set += $row;
	}
	
	public function update_row($index_name, $index_value, $row) {
		if (is_array($index_value)) {
			$index_value = static::$keys_delimeter . implode(static::$keys_delimeter, $index_value);
		}
		if (isset($this->indexes[$index_name], $this->indexes[$index_name][$index_value])) {
			$ids = $this->indexes[$index_name][$index_value];
		} else if ($index_name === 'id') {
			$ids = $index_value;
		}
		if (isset($ids)) {
			if (is_array($ids)) {
				foreach ($ids as $id) {
					if (isset($this->update_data[$id])) {
						$this->update_data[$id] = $this->update_data[$id] + $row;
					} else {
						$this->update_data[$id] = $row;
					}
				}
			} else {
				if (isset($this->update_data[$ids])) {
					$this->update_data[$ids] = $this->update_data[$ids] + $row;
				} else {
					$this->update_data[$ids] = $row;
				}
			}
		}
	}
	
	public function insert_or_update_row($index_name, $index_value, $row) {
		if (is_array($index_value)) {
			$index_value = static::$keys_delimeter . implode(static::$keys_delimeter, $index_value);
		}
		if (isset($this->indexes[$index_name][$index_value]) || ($index_name === 'id' && isset($this->data[$index_value]))) {
			$this->update_row($index_name, $index_value, $row);
		} else {
			$this->insert_row($index_name, $index_value, $row);
		}
	}
	
	public function do_updates() {
		$no_changes_ids = [];
		foreach ($this->update_data as $id => $row) {
			$this->touched_ids_set[$id] = true;
			if (isset($this->data[$id])) {
				$row = array_diff_assoc($row, $this->data[$id]);
			}
			if (empty($row)) {
				$no_changes_ids[] = $id;
				continue;
			}
			$query = 'update `' . et($this->table) . '` set ';
			foreach ($row as $key => $value) {
				$query .= et($key) . '=' . e($value) . ',';
			}
			$query .= '`updated_at`=now() where `id`=' . e($id);
			d()->db->exec($query);
		}
		if (!empty($no_changes_ids)) {
			$query = 'update `' . et($this->table) . '` set `updated_at`=now() where `id` in (' . implode(',', $no_changes_ids) . ')';
		}
	}
	
	public function do_inserts() {
		$inserted_ids = [];
		foreach ($this->insert_data as $row) {
			$query = 'insert into `' . et($this->table) . '` (`' . implode('`,`', array_map('et', array_keys($row))) . '`,`created_at`,`updated_at`) values (' . implode(',', array_map('e', $row)) . ',now(),now())';
			d()->db->exec($query);
			$id = d()->db->lastInsertId();
			$inserted_ids[] = $id;
			$this->touched_ids_set[$id] = true;
		}
		if (!empty($inserted_ids)) {
			d()->db->exec('update `' . et($this->table) . '` set `sort`=`id` where `id` in (' . implode(',', array_map('e', $inserted_ids)) . ')');
		}
	}
	
	public function do_bulk_inserts($count = 1000) {
		$inserted_ids = [];
		$inserting_rows = [];
		foreach ($this->insert_fields_set as $key => $value) {
			$this->insert_fields_set[$key] = null;
		}
		$i = count($this->insert_data);
		foreach ($this->insert_data as $row) {
			$inserting_rows[] = implode(',', array_map('e', $row + $this->insert_fields_set));
			if (!--$i || count($inserting_rows) === $count) {
				$query = 'insert into `' . et($this->table) . '` (`' . implode('`,`', array_map('et', array_keys($this->insert_fields_set))) . '`,`created_at`,`updated_at`) values (' . implode(',now(),now()),(', $inserting_rows) . ',now(),now())';
				d()->db->exec($query);
				$id = 1 * d()->db->lastInsertId();
				$query = 'select `id` from `' . et($this->table) . '` where `id`>=' . $id;
				$stmt = d()->db->query($query);
				while (($id = $stmt->fetch(PDO::FETCH_COLUMN)) !== false) {
					$inserted_ids[] = $id;
					$this->touched_ids_set[$id] = true;
				}
				$inserting_rows = [];
			}
		}
		if (!empty($inserted_ids)) {
			d()->db->exec('update `' . et($this->table) . '` set `sort`=`id` where `id` in (' . implode(',', array_map('e', $inserted_ids)) . ')');
		}
	}
	
	public function do_deletes() {
		$deleting_ids = array_keys(array_diff_key(array_flip(array_keys($this->data)), $this->touched_ids_set));
		if (!empty($deleting_ids)) {
			d()->db->exec('delete from `' . et($this->table) . '` where `id` in (' . implode(',', array_map('e', $deleting_ids)) . ')');
		}
	}
	
	public function add_key_callback($name, $callback) {
		$index = [];
		foreach ($this->data as $id => $row) {
			$callback($this->data, $id, $index);
		}
		$this->indexes[$name] = $index;
	}
	
}
