<?php

d()->route('/wtf', function ()
{
	$filename = __DIR__.'/../../storage/banner-01.jpg';

	if ($file_dimensions = getimagesize($filename)) {
		var_dump($file_dimensions);
		$file_type = strtolower($file_dimensions['mime']);
		switch ($file_type) {
			case 'image/gif':
				$img = ImageCreateFromGif($filename);
				break;
			case 'image/png':
				$img = ImageCreateFromPng($filename);
				imageSaveAlpha($img, true);
				break;
			case 'image/jpeg':
			case 'image/pjpeg':

				//var_dump(ImageCreateFromJpeg($filename));
				//exit;
				$img = ImageCreateFromJpeg($filename);
				if(function_exists("exif_read_data")){
					$exif = exif_read_data($filename);
					if(!empty($exif['Orientation'])) {
						switch($exif['Orientation']) {
							case 8:
								$img = imagerotate($img,90,0);
								$changed_rotation=true;
								break;
							case 3:
								$img = imagerotate($img,180,0);
								break;
							case 6:
								$img = imagerotate($img,-90,0);
								$changed_rotation=true;
								break;
						}
					}
				}
				break;
		}
	}

	var_dump($img);
});