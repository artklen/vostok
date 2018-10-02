<?php

class Product extends ActiveRecord
{
	function calc_price_format()
	{
		$res = $this->price * (1 - ($this->discount/100));
		return d()->price_format($res);
	}

	function price_origin_format()
	{
		return d()->price_format($this->price);
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