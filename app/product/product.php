<?php

class Product extends ActiveRecord
{
	use SmartImage;

	function title()
	{
		$t = $this->get('title');
		$clock = t('Часы') . ' ';

		// сложный непонятный говнокод
		if ($this->code && $this->category->is_gen)
		{
			if ($t)
				return $t;
			else
			{
				return $clock.$this->code." (".$this->collection->title.")";
			}
		}
		else
		{
			return ($temp = $this->get('title')) ? $temp : $this->excel_title;
		}
	}

	function calc_price_format()
	{
		return d()->price_format($this->price);
	}

	function price()
	{
		if ($this->discount)
		{
			return $this->discount;
		}
		else
		{
			return $res = $this->get('price');
		}
		# старая версия с %
		#return $res = $this->get('price') * (1 - ($this->discount / 100));
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

	function trigrams_string()
	{
		return $this->title;
	}
	function is_quantity()
	{
		$quantity = $this->get('quantity');
		if (is_numeric($quantity) && $quantity > 0){
			return true;
		}		
		if ($quantity == "Снят с производства"){
			return false;
		}
		return 0;
	}
	
	function admin_sort_title()
	{
		return $this->image_as_preview . ' <span style="font-size: 20px; display: inline-block; vertical-align: middle;">' . h($this->title) . '</span><br>';
	}
}