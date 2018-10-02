<?php

class Option_safe extends ActiveRecord {
	
	public function save()
	{
		$result = parent::save();
		if (!empty($_POST['exchange'])) {

			if (!empty($_POST['exchange']['excel_file'])) {
				$catalog_excel = new Catalog_excel();
				$catalog_excel->import_products($_POST['exchange']['excel_file']);
			}
			if (!empty($_POST['exchange']['images_zip_file'])) {
				$catalog_zip = new Catalog_zip();
				$catalog_zip->import_products_images($_POST['exchange']['images_zip_file']);
			}
		}
		d()->update_trigrams();
		return $result;
	}
	
}
