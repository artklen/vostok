<?php
class Review extends ActiveRecord
{
	use SmartImage;
	
	function show_is_published() {
		return $this->is_published
			? '<span style="color:#3c3;font-weight:bold;font-style:normal;font-size:1.5em;line-height:1em;">☑</span>'
			: '<span style="color:#999;font-weight:bold;font-style:normal;font-size:1.5em;line-height:1em;">☒</span>'; 
	}
	
	function show_product_id() {
		if ($this->product_id !== '' && $this->product->ne) {
			return '<a href="{langlink}' . d()->url_for($this->product) . '" target="_blank">' . $this->product->title . '</a>';
		}
		return '';
	}
}