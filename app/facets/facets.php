<?php

d()->facets = function($object, $fields = null, $params = null) {
	$class = get_class($object);
	if (!isset($fields)) {
		$fields = d()->{"{$class}__field"};
	}
	if (!isset($params)) {
		$params = new Get();
	}
	$filters = [];
	$facet_fields = [];
	$all_values = [];
	foreach ($fields as $field) {
		if (isset($field['field_name'], $field['filter_instance'])) {
			$name = $field['field_name'];
			$facet_fields[$name] = $field['filter_instance'];
			$filters[$name] = $facet_fields[$name];
			$sort_type = isset($field['sort_type']) ? $field['sort_type'] : '';
			$all_values[$name] = $filters[$name]->get_values(clone $object, $sort_type);
		}
	}
	$active_values = [];
	foreach ($facet_fields as $name => $field) {
		if (!empty($all_values[$name])) {
			$copy = clone $object;
			foreach ($filters as $filter_name => $filter) {
				if ($filter_name !== $name) {
					$copy = $filter->filter($copy, $params[$filter_name]);
				}
			}
			$sort_type = isset($fields[$name]['sort_type']) ? $fields[$name]['sort_type'] : '';
			$field_active_values = $filters[$name]->get_values($copy, $sort_type);
			if (isset($all_values[$name][0], $all_values[$name][0]['value'])) {
				$result_active_values = [];
				if (isset($all_values[$name])) {
					foreach ($all_values[$name] as $unfiltered_row) {
						foreach ($field_active_values as $filtered_row) {
							if ($unfiltered_row['value'] === $filtered_row['value']) {
								$result_active_values[] = $filtered_row;
								continue 2;
							}
						}
						$unfiltered_row['count'] = 0;
						$result_active_values[] = $unfiltered_row;
					}
				}
				$active_values[$name] = $result_active_values;
			} else {
				$active_values[$name] = $field_active_values;
			}
		}
	}
	$result_object = clone $object;
	foreach ($filters as $filter_name => $filter) {
		$result_object = $filter->filter($result_object, $params[$filter_name]);
	}
	return [$result_object, $all_values, $active_values];	
};
