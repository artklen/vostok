<?php

class Watchband extends ActiveRecord
{
	use SmartImage;

	function calc_price_format()
	{
		return d()->price_format($this->price);
	}

	function price()
	{
		return $res = $this->get('price') * (1 - ($this->discount / 100));
	}

	function price_origin_format()
	{
		return d()->price_format($this->get('price'));
	}

	function trigrams_string()
	{
		return $this->title;
	}
}