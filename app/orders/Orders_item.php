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

    public function price(): float
    {
        $stored = $this->get(__FUNCTION__);
        if ($stored !== '') {
            return 1. * $stored;
        }

		return 1. * $this->product->price + 1. * $this->products_variant->price;
	}

    public function price_with_discount(): float
    {
        $stored = $this->get(__FUNCTION__);
        if ($stored !== '') {
            return 1. * $stored;
        }

        $result = $this->price();

        /** @var Order $order */
        $order = $this->get('order');
        if ($order->ne()) {
            $discount_percent = $order->regular_customer_products_discount_percent();
            if ($discount_percent > 1e-7) {
                $result = $result * (100. - $discount_percent) / 100.;
            }
        }

        return round($result);
    }

	public function total_price(): float
    {
        $stored = $this->get(__FUNCTION__);
        if ($stored !== '') {
            return 1. * $stored;
        }

        return $this->price() * $this->number;
	}

    public function total_price_with_discount(): float
    {
        $stored = $this->get(__FUNCTION__);
        if ($stored !== '') {
            return 1. * $stored;
        }

        return $this->price_with_discount() * $this->number;
    }
}