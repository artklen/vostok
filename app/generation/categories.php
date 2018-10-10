<?php

d()->categories_seo_params = [
	'h1' => function($object) {
		return d()->categories_seo_data['h1'];
	},
	'title' => function($object) {
		return d()->categories_seo_data['title'];
	},
	'description' => function($object) {
		return d()->categories_seo_data['description'];
	},
	'canonical' => function($object) {
		return isset(d()->categories_seo_data['canonical']) ? d()->categories_seo_data['canonical'] : '';
	},
];

/*
 * d()->this_category - текущая категория
 * d()->products__fields - свойства товаров в фильтре
 * d()->get - значения свойств товаров на текущей странице (необязательно)
 */
d()->singleton('categories_seo_data', function() {
	$result = [];
	$category_url = d()->url_for(d()->this_category);
	$get = (d()->get !== '') ? d()->get : new Get();
	$canonical_get = new Get($category_url);
	$crumbs_get = new Get($category_url);
	//$crumbs_list = d()->tree_crumbs(d()->this_category);
	$crumbs_list = d()->page_crumb('/catalog');
	$title_arr = [];
	$special_title_arr = [];
	$flag_strs = [];
	$this_category_full_title = d()->full_title_of(d()->this_category);
	if (!empty(d()->products__fields)) {
		foreach (d()->products__fields as $products_filter_field) {
			$field_name = $products_filter_field['field_name'];
			if (!empty($get[$field_name])) {
				switch ($products_filter_field['type']) {
				case 'interval':
				case 'range':
					break;
				case 'boolean':
					$value = $get[$field_name];
					if ($value === ['1']) {
						if ($products_filter_field instanceof Products__field) {
							$value_title = $products_filter_field->title2;
						} else {
							$value_title = $products_filter_field['title'];
						}
						$flag_strs[] = $value_title;
						$crumbs_get->add($field_name . '[]', '1');
						$crumbs_list[] = [
							'title' => mb_strtoupper(mb_substr($value_title, 0, 1)) . mb_substr($value_title, 1),
							'link' => "$crumbs_get",
						];
						$canonical_get->add($field_name . '[]', '1');
					}
					break;
				case 'table':
					$value = $get[$field_name];
					$values_count = count($value);
					$model_name = (substr($field_name, -3) === '_id') ? substr($field_name, 0, -3) : ActiveRecord::plural_to_one($field_name);
					$model_name = strtoupper(substr($model_name, 0, 1)) . substr($model_name, 1);
					if ($values_count === 1) {
						$value = reset($value);
						$value_title = d()->$model_name->find_by('id', $value)->title;
						$title_arr[$field_name] = $value_title;
						$crumbs_get->add($field_name . '[]', str_replace('%20', '+', urlencode($value)));
						$crumbs_list[] = [
							'title' => mb_strtoupper(mb_substr($value_title, 0, 1)) . mb_substr($value_title, 1),
							'link' => "$crumbs_get",
						];
						$canonical_get->add($field_name . '[]', str_replace('%20', '+', urlencode($value)));
					} else {
						$values_arr = [];
						$special_values_arr = [];
						$values_arr = d()->$model_name->where('`id` in (?)', $value)->order('field(`id`,' . implode(',', array_map('e', $value)) . ')')->all_of('titles');
						$str = array_pop($values_arr);
						$title_arr[$field_name] = implode(', ', $values_arr) . ' и ' . $str;
					}
					break;
				case 'colors':
					$value = d()->get[$field_name];
					$values_count = count($value);
					if ($values_count === 1) {
						$value = reset($value);
						$color = d()->Products_color->find_by('id', $value);
						$value_title = $color->second_title !== '' ? $color->second_title : $color->title;
						$title_arr[$field_name] = $value_title;
						$crumbs_get->add($field_name . '[]', str_replace('%20', '+', urlencode($value)));
						$crumbs_list[] = [
							'title' => mb_strtoupper(mb_substr($value_title, 0, 1)) . mb_substr($value_title, 1),
							'link' => "$crumbs_get",
						];
						$canonical_get->add($field_name . '[]', str_replace('%20', '+', urlencode($value)));
					} else {
						$values_arr = [];
						$colors = d()->Products_color->where('`id` in (?)', $value);
						foreach ($colors as $color) {
							$values_arr[] = $color->second_title !== '' ? $color->second_title : $color->title;
						}
						$values_arr = array_unique($values_arr);
						$str = array_pop($values_arr);
						if (!empty($values_arr)) {
							$title_arr[$field_name] = implode(', ', $values_arr) . ' и ' . $str;
						} else {
							$title_arr[$field_name] = $str;
						}
					}
					break;
				default:
					$value = $get[$field_name];
					$values_count = count($value);
					if ($values_count === 1) {
						$value = reset($value);
						if ($products_filter_field instanceof Products__field) {
							$value_title = $products_filter_field->title_of($value);
							if ($products_filter_field->is_special_title($value)) {
								$special_title_arr[$field_name] = $value_title;
							} else {
								$title_arr[$field_name] = $value_title;
							}
						} else {
							$value_title = $value;
							$title_arr[$field_name] = $value_title;
						}
						$crumbs_get->add($field_name . '[]', str_replace('%20', '+', urlencode($value)));
						$crumbs_list[] = [
							'title' => mb_strtoupper(mb_substr($value_title, 0, 1)) . mb_substr($value_title, 1),
							'link' => "$crumbs_get",
						];
						$canonical_get->add($field_name . '[]', str_replace('%20', '+', urlencode($value)));
					} else {
						$values_arr = [];
						$special_values_arr = [];
						if ($products_filter_field instanceof Products__field) {
							foreach ($value as $str) {
								if ($products_filter_field->is_special_title($str)) {
									$special_values_arr[] = $products_filter_field->title_of($str);
								} else {
									$values_arr[] = $products_filter_field->title_of($str);
								}
							}
							if (!empty($special_values_arr)) {
								$str = implode(', ', array_map([$products_filter_field, 'title_of'], $special_values_arr));
								$str2 = '';
								if (!empty($values_arr)) {
									$str .= ', ' . mb_strtolower(mb_substr($this_category_full_title, 0, 1)) . mb_substr($this_category_full_title, 1) . ' ';
									$str2 = array_pop($values_arr);
									if (!empty($values_arr)) {
										$str2 = implode(', ', $values_arr) . ' и ' . $str2;
									}
								}
								$special_title_arr[$field_name] = $str . $str2;
							} else {
								$str = array_pop($values_arr);
								if (!empty($values_arr)) {
									$title_arr[$field_name] = implode(', ', $values_arr) . ' и ' . $str;
								}
							}
						} else {
							$values_arr = $value;
							$str = array_pop($values_arr);
							$title_arr[$field_name] = implode(', ', $values_arr) . ' и ' . $str;
						}
					}
					break;
				}
			}
		}
	}
	if (!empty($flag_strs)) {
		$last_flag_str = array_pop($flag_strs);
		$title_arr[] = (!empty($flag_strs) ? implode(', ', $flag_strs) . ' и ' : '') . $last_flag_str;
	}
	
	$copy = clone d()->products_list;
	$min_price = 1 * $copy->where('1*`price`>1e-7')->select('min(1*`price`) as `min`')->min;
	
	if (d()->this_category->page_title !== '') {
		$title = d()->this_category->page_title;
	} else {
		$title = $this_category_full_title;
		if (!empty($title_arr)) {
			$title .= ' ' . implode(' ', $title_arr);
		}
		if (trim($title) !== '') {
			$title .= ' ' . d()->current_city->second_title;
		}
	}
	
	// crumbs_list
	unset($crumbs_list[count($crumbs_list) - 1]['link']);
	$result['crumbs_list'] = $crumbs_list;
	
	// h1
	if (!empty($special_title_arr)) {
		$result['h1'] = implode(' ', $special_title_arr) . (!empty($title_arr) ? ' ' . implode(' ', $title_arr) : '');
		$result['h1'] = mb_strtoupper(mb_substr($result['h1'], 0, 1)) . mb_substr($result['h1'], 1);
	} else {
		$result['h1'] = $this_category_full_title . (!empty($title_arr) ? ' ' . implode(' ', $title_arr) : '');
	}
	
	// title
	if (!empty($special_title_arr)) {
		$result['title'] = implode(' ', $special_title_arr) . (!empty($title_arr) ? ' ' . implode(' ', $title_arr) : '');
		$result['title'] = mb_strtoupper(mb_substr($result['title'], 0, 1)) . mb_substr($result['title'], 1);
	} else {
		$result['title'] = $this_category_full_title . (!empty($title_arr) ? ' ' . implode(' ', $title_arr) : '');
	}
	$result['title'] .= ' - купить ' . d()->current_city->second_title;
	if (1 * $min_price > 1e-7) {
		$result['title'] .= ' по цене от ' . $min_price . ' руб.';
	}
	$result['title'] .= ' | ' . d()->Option->common_title;
	
	// description
	if (!empty($special_title_arr)) {
		$result['description'] = implode(' ', $special_title_arr) . (!empty($title_arr) ? ' ' . implode(' ', $title_arr) : '');
		$result['description'] = mb_strtoupper(mb_substr($result['description'], 0, 1)) . mb_substr($result['description'], 1);
	} else {
		$result['description'] = $this_category_full_title . (!empty($title_arr) ? ' ' . implode(' ', $title_arr) : '');
	}
	$result['description'] = '✔ ' . $result['description'] . ' купить ' . d()->current_city->second_title . ' по выгодной цене';
	if (1 * $min_price > 1e-7) {
		$result['description'] .= ' от ' . $min_price . ' руб.';
	}
	
	// canonical
	$copy = clone d()->products_list;
	if ($copy->limit(2)->count === 1) {
		$result['canonical'] = d()->url_for($copy);
	} else if ("$canonical_get" !== '' . d()->Get) {
		$result['canonical'] = "$canonical_get";
	}
	
	return $result;
});
