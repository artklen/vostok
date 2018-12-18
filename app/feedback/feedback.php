<?php

class Feedback extends ActiveRecord
{	
	function form_data_txt()
	{
		$res = $this->get('form_data');
		if ($res != ""){
			$res = json_decode($res, true);
			$output = implode('<br>', array_map(
				function ($v, $k) { return sprintf("%s='%s'", $k, $v); },
				$res,
				array_keys($res)
			));
		}
		return $output;
	}
}