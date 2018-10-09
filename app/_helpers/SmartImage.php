<?php

trait SmartImage
{
	function required_image()
	{
		$result = $this->image;
		if ($result === '') {
			$result = '/storage/nophoto.png';
		}
		return $result;
	}
}