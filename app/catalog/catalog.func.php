<?php

d()->route('/catalog', function ()
{
	$per_page = 18;

	d()->get = new Get();
	//$this_products = d()->this->subtree->_products;
	$this_products = d()->Product;
	$products__fields = d()->Products__field->only('filter')->all;
	array_unshift($products__fields, [
		'title'           => 'Цена, руб.',
		'field_name'      => 'price',
		'type'            => 'interval',
		'filter_instance' => new Products_filter_interval('price'),
	],
	/*[
		'title'           => 'Количество камней',
		'field_name'      => 'stones_count',
		'type'            => 'interval',
		'filter_instance' => new Products_filter_interval('stones_count'),
	],*/
	[
		'title'           => 'Коллекция',
		'field_name'      => 'collection_id',
		'type'            => 'table',
		'filter_instance' => new Products_filter('collection_id'),
	]
	/*,[
		'title'           => 'Форма',
		'field_name'      => 'products_shapes',
		'type'            => 'shapes',
		'filter_instance' => new Products_filter_relation([
			'table'      => 'products_to_products_shapes',
			'field_from' => 'product_id',
			'field_to'   => 'products_shape_id',
		]),
	]*/);

	$products__fields[] = [
		'field_name' => 'all_links',
		'type'       => 'all_links',
	];

	list(
		d()->this_products,
		$fields_data,
		$fields_filtered_data
	) = d()->facets(
		$this_products,
		$products__fields,
		d()->get
	);

	d()->products__fields = $products__fields;
	d()->fields_data = $fields_data;
	d()->fields_filtered_data = $fields_filtered_data;

	#d()->crumbs_list = d()->categories_seo_data['crumbs_list'];
	#d()->canonical = d()->categories_seo_data['canonical'];
	#d()->seo_from_object(d()->this, d()->categories_seo_params);

	if ($_REQUEST['order'] == 'price_to_min')
		d()->this_products->order('1*price desc');
	else
		d()->this_products->order('1*price asc');

	d()->this_products->paginate($per_page);

	if (d()->this_products->is_empty) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		header('Status: 404 Not Found');
	}

	d()->this = d()->this_products;

	d()->view->render('/pages/catalog.html');
});