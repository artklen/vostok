<?php

d()->route('/product/update_trigram',  function()
{
	if (isset($_GET['product_id'])) {
		$product_id = (int) $_GET['product_id'];
		d()->db->exec('delete from `products_trigrams` where `product_id`=' . $product_id);
		$product = d()->Product->find_by('id', $product_id);
		if ($product->ne) {
			$trigrams = get_trigram($this->title);
			if (!empty($trigrams)) {
				d()->db->exec('insert into `products_trigrams` (`product_id`, `value`) values (' . $product_id . ',' . implode('), (' . $product_id . ',', array_map(array(d()->db, 'quote'), $trigrams)) . ')');
			}
		}
	} else {
		d()->db->exec('truncate table `products_trigrams`');
		$stmt = d()->db->query('select `id`, `title` from `products`');
		while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
			$trigrams = get_trigram($row['title']);
			if (!empty($trigrams)) {
				d()->db->exec('insert into `products_trigrams` (`product_id`, `value`) values (' . $row['id'] . ',' . implode('), (' . $row['id'] . ',', array_map(array(d()->db, 'quote'), $trigrams)) . ')');
			}
		}
		print 'OK';
	}
	exit;
});

d()->route('/product/:url', function($url) {
	d()->this = d()->Product->find_by('url', $url);
	d()->crumbs_list = [d()->page_crumb('/catalog')];
	
	if (d()->this->collection->id)
		d()->crumbs_list[] = ['title' => d()->this->collection->title, 'link' => '/catalog?collection_id%5B%5D='. d()->this->collection->id];

	#d()->crumbs_list[] = d()->crumb_for(d()->this, false);
	d()->crumbs_list[] = ['title' => d()->this->code];
	d()->seo->h1 = "Часы ".d()->this->code." (".d()->this->collection->title.")";

	d()->view->render('/product/product.html');
});

d()->route('/product', function($url) {
	d()->page_not_found();
});