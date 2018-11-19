<?php

trait SmartImage
{
	function required_image()
	{
		$result = $this->image;
		#var_dump($result);
		#return $result;
		if ($result === '' || !file_exists(trim($result, '/'))) {
			$result = '/storage/nophoto.png';
		}
		return $result;
	}

	function required_image_full()
	{
		$result = $this->image_full;
		#return $result;
		if ($result === '') {
			$result = '/storage/nophoto.png';
		}
		return $result;
	}
}