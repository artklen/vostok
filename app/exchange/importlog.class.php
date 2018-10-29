<?php

class Importlog extends ActiveRecord
{
	function show_file()
	{
		if ($this->file !== '') {
			return '<a href="' . h($this->file) . '">' . h($this->file) . '</a><br>';
		}
		return '';
	}
	
	function show_import_type()
	{
		if ($this->import_type === 'import_products_excel') {
			return 'Импорт товаров из Excel';
		}
		if ($this->import_type === 'import_products_images') {
			return 'Импорт изображений из Zip-архива';
		}
		return '';
	}
}
