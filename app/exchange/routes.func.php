<?php

d()->route('/exchange/export_products/:id', function($id) {
	if (!iam()) {
		header('HTTP/1.0 403 Forbidden');
		exit;
	}
	$filename = str_replace(['/', '\\', ':', ';', '*', '"', '<', '>', '|'], [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '], 'products') . '.xlsx';
	$filepath = '/storage/export/' . $filename;
	$catalog_excel = new Catalog_excel();
	$catalog_excel->export_products($id, $filepath);

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($filename));
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	//header('Content-Length: ' . $item->size);
	print file_get_contents($_SERVER['DOCUMENT_ROOT'] . $filepath);
	//unlink($_SERVER['DOCUMENT_ROOT'] . $filepath);
	exit;

	#print d()->view->render('/exchange/export_products.html');
	exit;
});

d()->route('/exchange/import_products', function() {
	if (!iam()) {
		header('HTTP/1.0 403 Forbidden');
		exit;
	}

	var_dump('tes');die;
	if (!empty($_POST['excel_file'])) {
		$catalog_excel = new Catalog_excel();
		$catalog_excel->import_products($_POST['excel_file']);
	}
	if (!empty($_POST['images_zip_file'])) {
		$catalog_zip = new Catalog_zip();
		$catalog_zip->import_products_images($_POST['images_zip_file']);
	}

	print d()->view->render('/exchange/import_products.html');
	exit;
});
