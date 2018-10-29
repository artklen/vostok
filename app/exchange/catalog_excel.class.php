<?php
require_once __DIR__ . '/vendors/PHPExcel/Shared/String.php';
require_once __DIR__ . '/vendors/PHPExcel.php';
spl_autoload_unregister(['PHPExcel_Autoloader', 'Load']);
spl_autoload_register(['PHPExcel_Autoloader', 'Load'], false, true);

class Catalog_excel {
	
	public function import_products($filename) {
		set_time_limit(0);
		
		$log = d()->Importlog->new;
		$log->import_type = 'import_products_excel';
		$log->file = $filename;
		$log->save();
		
		if (!sbs($filename, 'http://') && !sbs($filename, 'https://')) {
			if (!sbs($filename, '/')) {
				$filename = "/{$filename}";
			}
			$filename = $_SERVER['DOCUMENT_ROOT'] . $filename;
		}
		
		$fields = d()->Products__field;
		$field_id_by_title = [];
		$field_names = [];
		$field_types_by_field_names = [];
		foreach ($fields as $field) {
			$field_id_by_title[$this->unicalize_field_title($field->title)] = $field->id;
			if ($field->unit !== '') {
				$field_id_by_title[$this->unicalize_field_title($field->title . ', ' . $field->unit)] = $field->id;
			}
			$field_names[$field->id] = $field->field_name;
			$field_types_by_field_names[$field->field_name] = $field->type;
		}
		
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = ['memoryCacheSize' => '8MB'];
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		
		$locale = 'ru_ru';
		$validLocale = PHPExcel_Settings::setLocale($locale);
		if (!$validLocale) {
			//echo 'Unable to set locale to ' . $locale . " - reverting to en_us" . PHP_EOL;
		}

		$inputFileType = PHPExcel_IOFactory::identify($filename);
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($filename);
		
		$objWorksheet = $objPHPExcel->getActiveSheet();
		$highestRow = $objWorksheet->getHighestRow();
		$highestColumn = PHPExcel_Cell::columnIndexFromString($objWorksheet->getHighestColumn());	
		$headerRow = 1;
		$headerTitles = [];
		$headerFields = [];
		$idField = 'id';
		$idTitle = 'id';
		$idFieldFix = 'code';

		$ownFields = [
			$this->unicalize_field_title($idTitle) => $idField,
			$this->unicalize_field_title('Артикул') => 'code',
			$this->unicalize_field_title('Название') => 'title',
			$this->unicalize_field_title('Изображение') => 'image',
			$this->unicalize_field_title('Цена') => 'price',
			$this->unicalize_field_title('Коллекция') => 'collection_name',
			$this->unicalize_field_title('Скидка') => 'discount',
			$this->unicalize_field_title('Выгоды') => 'benefits',
			#$this->unicalize_field_title('Свойства часов') => 'text_props',
			#$this->unicalize_field_title('Краткое описание') => 'text',
			#$this->unicalize_field_title('Полное описание') => 'text_full',
		];
		$ownColumns = [];
		$idColumn = null;
		$imagesField = null;
		for ($column = 0; $column < $highestColumn; ++$column) {
			$title = $this->unicalize_field_title($objWorksheet->getCellByColumnAndRow($column, $headerRow)->getValue());
			$headerTitles[$column] = $title;
			if ($title === $this->unicalize_field_title($idTitle)) {
				$idColumn = $column;
			} else if (isset($ownFields[$title])) {
				$ownColumns[$column] = $ownFields[$title];
			} else if (isset($field_id_by_title[$title])) {
				$headerFields[$column] = $field_names[$field_id_by_title[$title]];
			}
		}
		$last_id = null;
		$created_products_ids = [];
		$updated_products_ids = [];
		
		if (isset($idColumn)) {
			for ($row = $headerRow + 1; $row <= $highestRow; ++$row) {
				$values = [];
				$images_real = [];
				$products_colors_ids = null;
				$products_shapes_ids = null;
				$id = null;
				$collection_name = null;

				if (isset($idColumn)) {
					$id = $objWorksheet->getCellByColumnAndRow($idColumn, $row)->getValue();
				}

				/*if (trim($id) === '') {
					continue;
				}*/
				$is_string_empty = true;
				for ($column = 0; $column < $highestColumn; ++$column) {
					$value = trim($objWorksheet->getCellByColumnAndRow($column, $row)->getValue());
					if ($is_string_empty) {
						$is_string_empty = ($value === '');
					}
					if (isset($ownColumns[$column])) {
						switch ($ownColumns[$column]) {
							case 'text_import_value':
								$values[$ownColumns[$column]] = ($value === '' || $value === '-' || $value === '_') ? '' : $value;
								break;
							case 'price':
								if ($value !== '' && $value !== '-' && $value !== '_') {
									$value = sprintf('%.2f', str_replace(',', '.', $value));
									if (substr($value, -3) === '.00') {
										$value = substr($value, 0, -3);
									}
									$values[$ownColumns[$column]] = $value;
								} else {
									$values[$ownColumns[$column]] = '';
								}
								break;
							case 'image':
								if ($value !== '-' && $value !== '_' && $value !== '') {
									$images = slice_cell_value($value); // несколько изображений
									//$images = [$value]; // одно изображение
									foreach ($images as $image) {
										if (substr($image, 0, 1) === '/') {
											$images_real[$image] = $image;
										} else {
											$images_real[$image] = '/storage/import/' . md5($image) . strrchr($image, '.');
										}
									}

									if (!empty($images_real)) {
										$values[$ownColumns[$column]] = reset($images_real);
										$values[$ownColumns[$column] . '_import_value'] = $value;
										array_shift($images_real);
									}
								} else {
									$values[$ownColumns[$column]] = '';
									// $values[$ownColumns[$column] . '_import_value'] = '';
								}
								break;
							case 'collection_id':
								/*$values[$ownColumns[$column]] = d()->Collection->where('title = ?', $value)->id;
								break;*/
							case 'collection_name':
								$collection_name = $value;
								$collection = d()->Collection->where('title = ?', $collection_name);

								if ($collection->is_empty) {
									$collection->create([
										'title' => $collection_name,
									]);

									$collection = d()->Collection->where('title = ?', $collection_name);
								}

								#var_dump($collection_name);
								#var_dump($collection->to_sql);
								#die('NOO');
								break;
							default:
								$values[$ownColumns[$column]] = ($value === '' || $value === '-' || $value === '_') ? '' : $value;
								break;
						}
					} else if (isset($headerFields[$column])) {
						$field_name = $headerFields[$column];
						$value = trim($objWorksheet->getCellByColumnAndRow($column, $row)->getValue());
						if (isset($field_types_by_field_names[$field_name]) && $field_types_by_field_names[$field_name] === 'strings_list') {
							$value = implode("\n", slice_cell_value($value));
						}
						$values[$headerFields[$column]] = ($value === '' || $value === '-' || $value === '_') ? '' : $value;
					}
				}
				if ($is_string_empty) {
					continue;
				}
				/*if (isset($values['brand']) && d()->Brand->find_by('title', $values['brand'])->is_empty) {
					$brand = d()->Brand->new;
					$brand->title = $values['brand'];
					$brand->save();
					$created_brands_ids[] = $brand->insert_id;
				}*/
				
				$query_array = $values;

				$idValueFix = $query_array[$idFieldFix];

				if ($idValueFix && trim($idValueFix) !== '')
					$product_orm = d()->Product->find_by($idFieldFix, $idValueFix);

				if ($product_orm->$idFieldFix) {
					$query_strs = [];
					foreach ($query_array as $key => $value) {
						$query_strs[] = '`' . et($key) . '`=binary ' . e($value);
					}

					$query_strs[] = '`updated_at`=now()';

					if ($collection->id)
					{
						$query_strs[] = '`collection_id`='.$collection->id;
					}

					$query_strs[] = "url='product" . $product_id . "'";

					$q = 'update `products` set ' . implode(',', $query_strs) . ' where `id`=' . e($product_orm->id);

					d()->db->exec($q);
					$updated_products_ids[] = $last_id = $product_id = $product_orm->id;
				} else {
					#var_dump($query_array);die;
					$product_orm = d()->Product;
					$product_orm->create($query_array);
					$created_products_ids[] = $last_id = $product_id = $product_orm->insert_id;
					$product_orm = d()->Product->find_by($idField, $id);
					$product_orm->url = 'product' . $product_id;

					if ($collection->id)
					{
						$product_orm->collection_id = $collection->id;
					}

					$product_orm->save();
				}

				// кроме главного изображения есть дополнительные
				foreach ($images_real as $import_name => $image) {
					$image_orm = d()->Products_image->where('`product_id`=? and binary `image`= binary ?', $product_id, $image);
					if ($image_orm->is_empty) {
						$image_orm->create([
							'product_id' => $product_id,
							'image' => $image,
							'import_name' => $import_name,
						]);
					}
				}
			}
		}
		
		d()->is_imported = true;
		d()->created_products_list = d()->Product->where('`id` in (?)', $created_products_ids);
		d()->updated_products_list = d()->Product->where('`id` in (?)', $updated_products_ids);
		
		$objPHPExcel->disconnectWorksheets();
		unset($objPHPExcel);
		//$_SESSION['flash'] = 'Импорт успешно завершен.';
		//header('Location: ' . $_SERVER['REQUEST_URI']);
		//exit('[OK]');
	}
	
	public function export_products($filename) {
		static $fixed_columns = [
			'id' => 'id',
			'title' => 'Название',
			'image' => 'Изображение',
			'price' => 'Цена',
			'discount' => 'Скидка',
			'collection_id' => 'Коллекция',
			'benefits' => 'Выгоды',
			#'text' => 'Краткое описание',
			#'text_full' => 'Полное описание',
			#'text_props' => 'Свойства часов',
		];
		static $columns_callbacks = [];
		try {
			if (empty($columns_callbacks)) {
				$columns_callbacks = [
					'image' => 'export_field_import_value_callback',
					'text' => 'export_field_import_value_callback',
					'text_full' => 'export_field_import_value_callback',
					'collection_id' => 'export_collection_value',
				];
			}
			set_time_limit(0);
			
			PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, ['memoryCacheSize' => '8MB']);
			PHPExcel_Settings::setLocale('ru_ru');
			$php_excel = new PHPExcel();
			$worksheet = $php_excel->getActiveSheet();

			$products_list = d()->Product;
			$fields = d()->Products__field->all;
			$fields_by_names = [];
			foreach ($fields as $field) {
				$fields_by_names[$field->field_name] = $field;
			}
			$columns = $fixed_columns;
			foreach ($fields as $field) {
				$columns[$field->field_name] = $field->title;
			}

			$row = 1;
			$col = 0;
			foreach ($columns as $column_title) {
				$worksheet->setCellValueByColumnAndRow($col++, $row, $column_title);
			}
			$row++;
			foreach ($products_list as $product_orm) {
				$col = 0;
				foreach ($columns as $column_field => $column_title) {
					if (isset($columns_callbacks[$column_field])) {
						$value = $columns_callbacks[$column_field]($product_orm, $column_field);
					} else {
						$value = $product_orm->$column_field;
						if (isset($fields_by_names[$column_field]) &&
							($fields_by_names[$column_field]->type === 'strings_list')) {
							$value = str_replace("\n", ', ', $value);
						}
					}
					$worksheet->setCellValueByColumnAndRow($col++, $row, $value);
				}
				$row++;
			}
			
			if (sbs($filename, '/')) {
				$filename = "/{$filename}";
			}
			$filename = $_SERVER['DOCUMENT_ROOT'] . $filename;
			PHPExcel_IOFactory::createWriter($php_excel, 'Excel2007')->save($filename);
		} catch (Exception $e) {
			var_dump($e);
			exit;
		}
	}
	
	function unicalize_field_title($value) {
		return preg_replace('/(\s+)/i', '', str_replace([
			'а', 'е', 'ё', 'о', 'р', 'с', 'у', 'х', 'в', 'к', 'м', 'н', 'т', /*'ь', */'.', ',', ';', '/', '±', '*', "\n", "\r",
		], [
			'a', 'e', 'e', 'o', 'p', 'c', 'y', 'x', 'b', 'k', 'm', 'h', 't', /*'b', */'', '', '', '', '', '', '', '',
		], mb_strtolower($value, 'utf-8')));
	}
	
	function column_letters_by_index($i) {
		$i++;
		$result = '';
		do {
			$code = $i % 26;
			$result = chr($code + 64) . $result;
			$i = ($i - $code) / 26;
		} while ($i);
		return $result;
	}
	
}

function export_field_import_value_callback($orm, $field) {
	$real_field = $field . '_import_value';
	$result = $orm->$real_field;
	if ($result === '') {
		$result = $orm->$field;
	}
	return $result;
}

function export_field_boolean_callback($orm, $field_name) {
	return $orm->$field_name ? '1' : '';
}

function export_field_safe_callback($orm, $field_name) {
	return $orm->get($field_name);
}

function export_field_objects_titles_callback($orm, $field) {
	return implode(',', $orm->$field->fast_all_of('title'));
}

function orm_field_callback_generator($field) {
	$_field = $field;
	return function($orm, $field_name) use ($_field) {
		$object = $orm->$field_name;
		if ($object !== '' && $object->ne) {
			return $object->$_field;
		}
		return '';
	};
}

function slice_cell_value($value) {
	$result = [];
	$value = str_replace(',' ,"\n", $value);
	$value = str_replace(';', "\n", $value);
	$replaces_count = 0;
	do {
		$value = str_replace("\n\n", "\n", $value, $replaces_count);
	} while ($replaces_count);
	$parts = explode("\n", $value);
	foreach ($parts as $part) {
		if (($part = trim($part)) === '') {
			continue;
		}
		$result[] = $part;
	}
	return $result;
}

function export_collection_value($orm, $field)
{
	$result = $orm->collection->title;
	return $result;
}