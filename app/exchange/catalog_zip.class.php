<?php

class Catalog_zip
{
	function import_products_images($filename)
	{
		static $exts = array(
			'.jpg' => 1,
			'.jpeg' => 1,
			'.png' => 1,
			'.gif' => 1,
			'.bmp' => 1,
		);

		$log = d()->Importlog->new;
		$log->import_type = 'import_products_images';
		$log->file = $filename;
		$log->save();

		if (isset($filename) && substr($filename, 0, 7) !== 'http://')
		{
			if ($filename{0} !== '/')
			{
				$filename = "/{$filename}";
			}

			$filename = "{$_SERVER['DOCUMENT_ROOT']}{$filename}";
		}

		$zip = new ZipArchive();

		if ($zip->open($filename) !== true)
		{
			return;
		}

		$numFiles = $zip->numFiles;

		for ($i = 0; $i < $numFiles; $i++)
		{
			$stat = $zip->statIndex($i);
			$name = $stat['name'];
			$ext = strrchr($name, '.');
			if (isset($exts[strtolower($ext)]))
			{
				$path =
					"{$_SERVER['DOCUMENT_ROOT']}/storage/import/"
					.md5($name).$ext;

				copy("zip://{$filename}#{$name}", $path);
				$this->fix_image($path);
			}
		}
		//exit;
	}

	function fix_image($filename)
	{
		$imagetypes = array(
			'gif' => IMAGETYPE_GIF,
			'jpeg' => IMAGETYPE_JPEG,
			'jpg' => IMAGETYPE_JPEG,
			'png' => IMAGETYPE_PNG
		);

		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$exif = exif_imagetype($filename);
		if (isset($imagetypes[$extension]) && $exif && ($exif !== $imagetypes[$extension]))
		{
			if ($exif === IMAGETYPE_JPEG)
			{
				$img = imagecreatefromjpeg($filename);
			}
			else if ($exif === IMAGETYPE_GIF)
			{
				$img = imagecreatefromgif($filename);
			}
			else if ($exif === IMAGETYPE_PNG)
			{
				$img = imagecreatefrompng($filename);
			}
			else
			{
				return $filename;
			}

			if ($extension === 'jpg' || $extension === 'jpeg')
			{
				imagejpeg($img, $filename, 90);
			}
			else if ($extension === 'gif')
			{
				imagegif($img, $filename);
			}
			else if (extension === 'png')
			{
				imagepng($img, $filename);
			}
		}
		return $filename;
	}

}