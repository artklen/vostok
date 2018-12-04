<?php
d()->categories_url_base = '/catalog/';

d()->route('/catalog', function ()
{
	$per_page = 18;

	d()->get = new Get();
	//$this_products = d()->this->subtree->_products;

	if ($str = $_GET['search'])
	{
		$this_products = d()->Product->where('excel_title like ? or code like ?', "%$str%", "%$str%");
	}
	elseif ($str = $_GET['searchtrigram'])
	{
		$trigrams = get_trigram($str);
		$ids = d()->Products_trigram->select('`product_id`,count(*) as `c`')->where('`value` in (?)', $trigrams)->group_by('`product_id`')->order_by('`c` desc')->limit(8)->fast_all_of('product_id');
		if (!empty($ids)) {
			$this_products = d()->Product->where('`id` in (?)', $ids)->order_by('field(id,' . implode(',', $ids) . ')');
		} else {
			$this_products = d()->Product->where('false');
		}

		d()->this_products = $this_products;
		$this_products_array = d()->this_products->to_array();

		foreach ($this_products_array as &$tt)
		{
			$tt['title'] = d()->Product->where('id = ?', $tt['id'])->title;
			$tt['link'] = '/product/'.$tt['url'];
		}

		print json_encode($this_products_array);
		#var_dump(d()->this_products->all);die;
		#print d()->view->partial('/app/catalog/_search_popup.html');
		exit;
	}
	else
	{
		d()->page_not_found();
		$this_products = d()->Product;
	}

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

	d()->unfiltered_products_list = $this_products;

	#var_dump($products__fields);die;
	list(
		d()->this_products,
		$fields_data,
		$fields_filtered_data
	) = d()->facets(
		$this_products,
		$products__fields,
		d()->get
	);

	#var_dump($fields_data);
	#var_dump($fields_filtered_data);
	#die;


	/*if (!empty(d()->get[$name]) && is_array(d()->get[$name]) && in_array(~data['value'], ~get[~name]))*/

	$selected = [];
	# поднимаем вверх выбранные элементы


	foreach ($fields_filtered_data as $name => &$fields_filtered)
	{
		#var_dump($name);
		#var_dump($fields_filtered);
		#die;

		/*
		 пропускаем если это диапазон

		 array(2) {
		  ["min"]=>
		  string(6) "500000"
		  ["max"]=>
		  string(6) "500000"
		}
		 */
		if (!isset($fields_filtered[0]))
			continue;

		foreach ($fields_filtered as $key => &$value)
		{
			#var_dump($name);
			#var_dump(d()->get[$name]);
			#var_dump($value);
			#die;

			if ($key === 0)
			{
				continue;
			}

			if ((is_array(d()->get[$name]) && in_array($value['value'], d()->get[$name]) || (d()->get[$name] == $value['value'])))
			{
				$selected[$name][] = $fields_filtered[$key];
				unset($fields_filtered[$key]);
			}
		}
	}


	#var_dump($selected);die;

	foreach ($selected as $name => $value)
		foreach ($value as $v)
		array_unshift($fields_filtered_data[$name], $v);

	#var_dump($fields_filtered_data);die;

	d()->products__fields = $products__fields;
	d()->fields_data = $fields_data;
	d()->fields_filtered_data = $fields_filtered_data;

	var_dump(d()->fields_filtered_data);die;

	#var_dump(d()->fields_filtered_data);die;

	#d()->crumbs_list = d()->categories_seo_data['crumbs_list'];
	#d()->canonical = d()->categories_seo_data['canonical'];
	#d()->seo_from_object(d()->this, d()->categories_seo_params);

	if ($_REQUEST['order'] == 'price_to_min')
		d()->this_products->order('1*price desc');
	else
		d()->this_products->order('1*price asc');

	d()->count_this_products = d()->this_products->count;
	d()->this_products->paginate($per_page);

	if (d()->this_products->is_empty) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		header('Status: 404 Not Found');
	}

	d()->this = d()->this_products;

	if (d()->get->collection_id && count(d()->get->collection_id === 1)) {
		d()->this_page = d()->Collection->f(d()->get->collection_id);
	}
	if (!d()->this_page || d()->this_page->is_empty) {
		d()->this_page = d()->Page->find_by('url', 'catalog');
	}

	if (isset($_GET['search'])) {
		d()->set_page_title('Результаты поиска по запросу «' . $str . '»');
		array_unshift(d()->crumbs_list, d()->page_crumb('/catalog'));
		d()->canonical = '';
		unset(d()->crumbs_list[0]);
	} else {
		d()->crumbs_list = d()->catalog_seo_data['crumbs_list'];
		d()->canonical = d()->catalog_seo_data['canonical'];
		d()->seo_from_object(d()->this_page, d()->catalog_seo_params);
	}

	d()->view->render('/catalog/catalog.html');
});

d()->route('/catalog/:category', function ($category)
{
	$per_page = 18;
	d()->get = new Get();
	d()->this_category = d()->Category->where('url = ?', $category);

	if (!d()->this_category->ne)
	{
		d()->page_not_found();
	}

	if ($str = $_GET['search'])
	{
		$this_products = d()->this_category->products->where('title like ?', "%$str%");
	}
	elseif ($str = $_GET['searchtrigram'])
	{
		$trigrams = get_trigram($str);

		$ids = d()->this_category
			->products
			->select('`product_id`,count(*) as `c`')
			->where('`value` in (?)', $trigrams)
			->group_by('`product_id`')
			->order_by('`c` desc')
			->limit(8)
			->fast_all_of('product_id');

		if (!empty($ids)) {
			$this_products = d()->this_category
				->products
				->where('`id` in (?)', $ids)
				->order_by('field(id,' . implode(',', $ids) . ')');
		} else {
			$this_products = d()->this_category->products->where('false');
		}

		d()->this_products = $this_products;
		$this_products_array = d()->this_products->to_array();

		foreach ($this_products_array as &$tt)
			$tt['link'] = '/watchband/'.$tt['url'];

		print json_encode($this_products_array);
		exit;
	}
	else
	{
		#var_dump(d()->this_category->all);die;
		#$this_products = d()->this_category->products;
		$this_products = d()->Product->where('category_id = ?', d()->this_category->id);
	}

	#var_dump($this_products->all);die;

	$products__fields = d()->Products__field->only('filter')->all;

	array_unshift($products__fields,
		[
			'title'           => 'Цена, руб.',
			'field_name'      => 'price',
			'type'            => 'interval',
			'filter_instance' => new Products_filter_interval('price'),
		],
		[
			'title'           => 'Коллекция',
			'field_name'      => 'collection_id',
			'type'            => 'table',
			'filter_instance' => new Products_filter('collection_id'),
		]
	);

	$products__fields[] = [
		'field_name' => 'all_links',
		'type'       => 'all_links',
	];

	d()->unfiltered_products_list = $this_products;

	list(
		d()->this_products,
		$fields_data,
		$fields_filtered_data
		) = d()->facets(
		$this_products,
		$products__fields,
		d()->get
	);

	$selected = [];
	# поднимаем вверх выбранные элементы

	foreach ($fields_filtered_data as $name => &$fields_filtered)
	{
		if (!isset($fields_filtered[0]))
			continue;

		foreach ($fields_filtered as $key => &$value)
		{
			if ($key === 0)
			{
				continue;
			}

			if ((is_array(d()->get[$name]) && in_array($value['value'], d()->get[$name]) || (d()->get[$name] == $value['value'])))
			{
				$selected[$name][] = $fields_filtered[$key];
				unset($fields_filtered[$key]);
			}
		}

		unset($value);
	}

	foreach ($selected as $name => $value)
		foreach ($value as $v)
			array_unshift($fields_filtered_data[$name], $v);

	d()->products__fields = $products__fields;
	d()->fields_data = $fields_data;
	d()->fields_filtered_data = $fields_filtered_data;

	if ($_REQUEST['order'] == 'price_to_min')
		d()->this_products->order('1*price desc');
	else
		d()->this_products->order('1*price asc');

	d()->this_products->order('sort');

	d()->count_this_products = d()->this_products->count;
	d()->this_products->paginate($per_page);

	if (d()->this_products->is_empty) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		header('Status: 404 Not Found');
	}

	d()->this = d()->this_products;

	if (d()->get->collection_id && count(d()->get->collection_id === 1)) {
		d()->this_page = d()->Collection->f(d()->get->collection_id);
	}
	if (!d()->this_page || d()->this_page->is_empty) {
		d()->this_page = d()->Page->find_by('url', 'catalog');
	}

	if (isset($_GET['search'])) {
		d()->set_page_title('Результаты поиска по запросу «' . $str . '»');
		array_unshift(d()->crumbs_list, d()->page_crumb('/catalog'));
		d()->canonical = '';
		d()->crumbs_list = [];
	} else {
		d()->crumbs_list = d()->catalog_seo_data['crumbs_list'];
		d()->canonical = d()->catalog_seo_data['canonical'];
		d()->seo_from_object(d()->this_page, d()->catalog_seo_params);
	}

	if (d()->this_category->ne)
	{
		d()->crumbs_list[0] =
		[
			'title' => d()->this_category->title
		];

		if (count($_GET) > 0)
		{
			d()->crumbs_list[0]['link'] = '/catalog/'.d()->this_category->url;
		}
	}

	d()->view->render('/catalog/catalog.html');
});