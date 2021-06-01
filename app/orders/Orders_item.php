<?php

/**
 * @property string number
 */
class Orders_item extends ActiveRecord {
	
	protected function get_own_or_products_variant_or_product_value($name) {
		$result = $this->get($name);
		if (($result === '') && ($products_variant = $this->products_variant) && $products_variant->ne) {
			$result = $products_variant->$name;
		}
		if (($result === '') && ($product = $this->product) && $product->ne) {
			$result = $product->$name;
		}
		return $result;
	}
	
	function link() {
		$result = $this->get(__FUNCTION__);
		if (($result === '') && ($product = $this->product) && $product->ne) {
			$result = d()->url_for($product);
		}
		return $result;
	}
	
	function title() {
		$result = $this->get(__FUNCTION__);
		if ($result === '') {
			if (($product = $this->product) && $product->ne) {
				$result = $product->title;
			}
			if (($products_variant = $this->products_variant) && $products_variant->ne) {
				$result .= ' (' . $products_variant->title . ')';
			}
		}
		return $result;
	}
	
	function product_title() {
		$result = $this->get(__FUNCTION__);
		if (($result === '') && ($product = $this->product) && $product->ne) {
			$result = $product->title;
		}
		return $result;
	}
	
	function products_variant_title() {
		$result = $this->get(__FUNCTION__);
		if (($result === '') && ($products_variant = $this->products_variant) && $products_variant->ne) {
			$result = $products_variant->title;
		}
		return $result;
	}
	
	function weight() {
		return $this->get_own_or_products_variant_or_product_value(__FUNCTION__);
	}
	
	function netto() {
		return $this->products_variant->netto;
	}
	
	function image() {
		return $this->get_own_or_products_variant_or_product_value(__FUNCTION__);
	}
	
	function lead() {
		$result = $this->get(__FUNCTION__);
		if (($result === '') && ($product = $this->product) && $product->ne) {
			$result = $product->lead;
		}
		return $result;
	}
	
	function category_id() {
		$result = $this->get(__FUNCTION__);
		if (($result === '') && ($product = $this->product) && $product->ne) {
			$result = $product->category_id;
		}
		return $result;
	}
	
	function category() {
		return d()->Category->find_by('id', $this->category_id);
	}
	
	function price() {
		return 1 * $this->product->price + 1 * $this->products_variant->price;
	}

	function total_price() {
		$result = 1 * $this->get(__FUNCTION__);
		if ($result < 1e-7) {
			$result = $this->price * $this->number;
		}
		return $result;
	}
	
}