<?php

class Importlog extends ActiveRecord
{
	function hyperlink()
	{
		if ($this->file !== '') {
			return '<a href="' . h($this->file) . '">' . h($this->file) . '</a><br>';
		}
		return '';
	}
	
	function catlink()
	{
		if (($this->category_id !== '') && $this->category->ne) {
			return '<a target="_blank" href="' . d()->h_url_for($this->category) . '">' . h($this->category->full_title) . '</a>';
		}
		return '';
	}
	
}
