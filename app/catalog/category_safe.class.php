<?php

class Category_safe extends ActiveRecord {
	public function save()
	{
		$result = parent::save();
		if (!empty($_POST['exchange'])) {
			$id = $this->insert_id ? $this->insert_id : $this->id;
			if (!empty($_POST['exchange']['excel_file'])) {
				$catalog_excel = new Catalog_excel();
				$catalog_excel->import_products($id, $_POST['exchange']['excel_file']);
			}
			if (!empty($_POST['exchange']['images_zip_file'])) {
				$catalog_zip = new Catalog_zip();
				$catalog_zip->import_products_images($id, $_POST['exchange']['images_zip_file']);
			}
		}
		d()->update_trigrams();
		return $result;
	}
	
}
