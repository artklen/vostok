<?php

/* Пример использования:

$title = 'Железобетонный блок';

//Добавление
$product = d()->Product->new;
$product->title =$title;
$product->url = d()->get_unique_field( d()->transliterate_url( $title), 'products', 'url');
$product->save();

//Обновление
$product = d()->Product->find(23);
$product->title =$title;
$product->url = d()->get_unique_field( d()->transliterate_url( $title), 'products', 'url', $product->id);
$product->save();


*/ 

d()->get_unique_field = function($title, $table, $field,$id="new"){

	if( $title == ''){
		return '';
	}

	$urls_set = array();

	foreach($data as $key=>$value){
		if(isset($value[ $field ]) && $value[ $field ]!=''){
			$urls_set[$value[ $field ]] = $value[ $field ];
		}
	}
	 
	
	$url = $title;
	$checked_urls = array();
	if(is_numeric($id)){
	 	$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where id != '.e($id). ' AND `'.et($field).'` = '.e($url).' limit 1')->fetchAll(PDO::FETCH_ASSOC);
	}else{
	 	$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where  `'.et($field).'` = '.e($url).  ' limit 1')->fetchAll(PDO::FETCH_ASSOC);
	}
	$check_first_url = (count($data)!=0);
	
	if ($check_first_url) {
		$checked_urls[$url] = true;
		$url .= '-2';
		
		if(is_numeric($id)){
			$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where id != '.e($id). ' AND `'.et($field).'` = '.e($url).' limit 1')->fetchAll(PDO::FETCH_ASSOC);
		}else{
			$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where  `'.et($field).'` = '.e($url).  ' limit 1')->fetchAll(PDO::FETCH_ASSOC);
		}
		$check_first_url = (count($data)!=0);
		
		while ($check_first_url  && !isset($checked_urls[$url])) {
			
			$checked_urls[$url] = true;
			$suffix = ltrim(strrchr($url, '-'), '-');
			 
			$url = substr($url, 0, - (strlen($suffix)+1)) . '-' . (1 * $suffix + 1);
			
			if(is_numeric($id)){
				$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where id != '.e($id). ' AND `'.et($field).'` = '.e($url).' limit 1')->fetchAll(PDO::FETCH_ASSOC);
			}else{
				$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where  `'.et($field).'` = '.e($url).  ' limit 1')->fetchAll(PDO::FETCH_ASSOC);
			}
			$check_first_url = (count($data)!=0);

		}
		
	}
	return $url;
};

d()->get_unique_field_by_index = function($value, $index, $id = null) {
	$checked_values_set = [];
	if (isset($index[$value]) && ($index[$value] !== $id)) {
		$checked_values_set[$value] = true;
		$value .= '-2';
		while (isset($index[$value]) && ($index[$value] !== $id) && !isset($checked_values_set[$value])) {
			$checked_values_set[$value] = true;
			$suffix = ltrim(strrchr($value, '-'), '-');
			$value = substr($value, 0, - (strlen($suffix) + 1)) . '-' . (1 * $suffix + 1);
		}
	}
	return $value;
};

d()->get_unique_url_by_index = function($value, $index, $id = null) {
	return d()->get_unique_field_by_index(d()->transliterate_url($value), $index, $id);
};
