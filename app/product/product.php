<?php

class Product extends ActiveRecord
{
	function calc_price_format()
	{
		return d()->price_format($this->price);
	}

	function price()
	{
		return $res = $this->get('price') * (1 - ($this->discount/100));
	}

	function price_origin_format()
	{
		return d()->price_format($this->get('price'));
	}

	function props()
	{
		$a = preg_split("/[\n\r]|\n\r|<br ?\/?>/", $this->text_props);

		foreach ($a as $k => &$b)
		{
			if (!$a[$k])
			{
				unset($a[$k]);
				continue;
			}

			$c = trim(strip_tags($b));
			$b = [];
			$b['title'] = $c;
		}

		return $a;
	}

	function similar_products()
	{

	}
}