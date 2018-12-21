<?php

class Basket extends UniversalSingletoneHelper
{
	
	public $order = null;
	
	protected function init_order()
	{
		$order = d()->Order->where('`session_key`=? and `status_id` is null', session_id())->order('`created_at` desc')->limit(1);
		if ($order->ne) {
			$this->order = $order;
		}
	}
	
	function current()
	{
		if (!isset($this->order)) {
			$this->init_order();
		}
		return $this;
	}
	
	function create_order_if_not_exists()
	{
		if (!isset($this->order)) {
			$order = d()->Order->new;
			$order->session_key = session_id();
			$order->save();
			$this->order = d()->Order->find_by('id', $order->insert_id);
		}
	}
	function delivery($params)
	{
		if (isset($params['delivery_id']) && $params['delivery_id']!= ""){
			$_SESSION['delivery_id'] = $params['delivery_id'];
		}
	}
	
	function add_item($params, $number = null)
	{
		$item_key = $this->item_key_of($params);
		if (!isset($number)) {
			$number = $this->get_number_from($params);
		}
		if (!$number) {
			$number = 1;
		}
		$item = $this->find_or_create_item($params);
		$item->number = $item->number + $number;
		$item->save();
	}
	
	function update_item($params, $number = null)
	{
		$item_key = $this->item_key_of($params);
		if (!isset($number)) {
			$number = $this->get_number_from($params);
		}
		if (1 * $number < 1e-7) {
			//$this->delete_item($params);
			$number = 1;
		}
		$item = $this->find_or_create_item($params);
		$item->number = $number;
		$item->save();
	}
	
	function delete_item($params)
	{
		$item = $this->find_item($this->item_key_of($params));
		if ($item->ne) {
			$item->delete();
		}
	}
	
	function find_or_create_item($params)
	{
		$result = $this->find_item($params);
		if ($result->is_empty) {
			$result = $this->create_item($params);
		}
		return $result;
	}
	
	function orders_items()
	{
		if (isset($this->order)) {
			return d()->Orders_item->where('`order_id`=?', $this->order->id);
		}
		return d()->Orders_item->where('false');
	}
	
	function find_item($item_key)
	{
		if ($item_key instanceof Orders_item) {
			return $item_key;
		}
		if (is_array($item_key) || is_object($item_key)) {
			$item_key = $this->item_key_of($item_key);
		}
		return $this->orders_items->where('`item_key`=?', $item_key);
	}
	
	function create_item($params)
	{
		static $white_list = array('product_id', 'products_variant_id');
		$this->create_order_if_not_exists();
		$item_key = $this->item_key_of($params);
		$arr = array(
			'item_key' => $item_key,
			'order_id' => $this->order->id,
		);
		if (is_object($params)) {
			$is_ar = $params instanceof ActiveRecord;
			foreach ($white_list as $key) {
				if ($is_ar || isset($params->$key) || property_exists($params, $key)) {
					$arr[$key] = $params->$key;
				}
			}
		} else {
			foreach ($white_list as $key) {
				if (isset($params[$key])) {
					$arr[$key] = $params[$key];
				}
			}
		}
		return d()->Orders_item->find_by_id(d()->Orders_item->create($arr)->insert_id);
	}
	
	function get_number_from($params)
	{
		if (is_array($params)) {
			if (isset($params['number'])) {
				return (int) $params['number'];
			}
		} else if (is_object($params)) {
			if (($params instanceof ActiveRecord) || isset($params->number) || property_exists($params, 'number')) {
				return (int) $params->number;
			}
		}
		return 0;
	}
	
	function clear()
	{
		if (isset($this->order)) {
			d()->db->exec('delete from `orders_items` where `order_id`=' . d()->db->quote($this->order->id));
		}
		unset($_SESSION['delivery_id']);
	}
		
	function total_number($item_key = null)
	{
		$result = 0;
		if (is_array($item_key) || is_object($item_key)) {
			$item_key = $this->item_key_of($item_key);
		}
		$items = $this->items($item_key);
		foreach ($items as $item) {
			$result += $items->number;
		}
		return $result;
	}

	function total_price($item_key = null)
	{
		$result = 0;
		if (is_array($item_key) || is_object($item_key)) {
			$item_key = $this->item_key_of($item_key);
		}
		$items = $this->items($item_key);
		#var_dump($items->all);die;
		foreach ($items as $item) {
			$result += $item->number * $item->price;
		}
		if (isset($_SESSION['delivery_id']) && $_SESSION['delivery_id']!= ""){
			$delivery = d()->Delivery_variant->find_by_id($_SESSION['delivery_id']);
			if ($delivery->ne){
				if ($delivery->free_price != "" && $delivery->free_price*1 <= $result){
					return $result;
				}
				if ($delivery->price != "" && $delivery->price*1 >0){
					return ($result+$delivery->price);
				}
			}
		}
		return $result;
	}
	
	function products_price()
	{
		static $result;
		if (!isset($result)) {
			$result = $this->total_price();
		}
		return $result;
	}
	
	function order_price()
	{
		static $result;
		if (!isset($result)) {
			$result = $this->products_price();
		}
		return $result;
	}

	function total_weight($item_key = null)
	{
		$result = 0;
		if (is_array($item_key) || is_object($item_key)) {
			$item_key = $this->item_key_of($item_key);
		}
		$items = $this->items($item_key);
		foreach ($items as $item) {
			$result += $item->number * $item->weight;
		}
		return $result;
	}
	
	function product_total_number($product_id)
	{
		$result = 0;
		$product_items = $this->product_items($product_id);
		foreach ($product_items as $item) {
			$result += $item->number;
		}
		return $result;
	}
	
	function product_total_price($product_id)
	{
		$result = 0;
		$product_items = $this->product_items($product_id);
		foreach ($product_items as $item) {
			$result += $item->number * $item->price;
		}
		return $result;
	}
	
	function product_total_weight($product_id)
	{
		$result = 0;
		$product_items = $this->product_items($product_id);
		foreach ($product_items as $item) {
			$result += $item->number * $item->weight;
		}
		return $result;
	}
	
	function items($item_key = null) {
		$result = $this->orders_items;
		if (isset($item_key)) {
			if (is_array($item_key) || is_object($item_key)) {
				$item_key = $this->item_key_of($item_key);
			}
			$result->where('`item_key`=?', $item_key);
		}
		return $result;
	}
	
	function product_items($product_id) {
		if (is_array($product_id)) {
			$product_id = $product_id['id'];
		} else if (is_object($product_id)) {
			$product_id = $product_id->id;
		}
		return $this->orders_items->where('`product_id`=?', $product_id);
	}
	
	function products_items($products) {
		$ids = array();
		foreach ($products as $product) {
			if (is_array($product)) {
				$id = $product['id'];
			} else if (is_object($product)) {
				$id = $product->id;
			} else {
				$id = "$product";
			}
			$ids[$id] = $id;
		}
		return $this->orders_items->where('`product_id` in (?)', $ids);
	}
	
	public function item_keys() {
		return $this->items->fast_all_of('item_key');
	}
	
	/*
	 * Определения уникального ключа, идентифицирующего товар в корзине
	 */
	function item_key_of($params) {
		if ($params instanceof Orders_item) {
			return $params['item_key'];
		}
		$result = '';
		if (isset($params['product_id']) && $params['product_id'] !== '') {
			$result .= 'p' . $params['product_id'];
		}
		if (isset($params['products_variant_id']) && $params['products_variant_id'] !== '') {
			$result .= 'v' . $params['products_variant_id'];
		}
		return $result;
	}
	
}
