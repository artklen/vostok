<?php

class Product_safe extends ActiveRecord {

	public function save()
	{
		$result = parent::save();
		if (!empty($this->_data)) {
			$item = d()->Product->find_by('id', $this->_data[0]['id']);
			if ($item->title !== '') {
				$id = $item->id;
				$title = $item->title;
				d()->db->exec('delete from `products_trigrams` where `product_id`=' . $id);
				$trigrams = get_trigram($title);
				if (!empty($trigrams)) {
					d()->db->exec('insert into `products_trigrams` (`product_id`, `value`) values (' . $id . ',' . implode('), (' . $id . ',', array_map(array(d()->db, 'quote'), $trigrams)) . ')');
				}
			}
		}
		return $result;
	}

	/* вот только сейчас она не вызывается */
	public function delete()
	{
		d()->db->exec('delete from `products_trigrams` where `product_id`=' . $this->id);
		return parent::delete();
	}

}