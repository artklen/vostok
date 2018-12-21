<?php

class Order extends ActiveRecord
{
	const STARTED    = 0;
	const CREATED    = 1;
	const PROCESSED  = 2;
	const PACKAGED   = 3;
	const WAITING_TC = 4;
	const WAITING_DS = 5;
	const CANCELED   = 6;
	const SENT       = 7;
	const COMPLETE   = 8;
	
	public static $status_titles = array(
		0 => 'Набирается',
		1 => 'Принята заявка',
		2 => 'В работе',
		3 => 'Скомплектован',
		4 => 'Ожидается забор ТК',
		5 => 'Ожидается забор службы доставки',
		6 => 'Отказ',
		7 => 'Отправлен',
		8 => 'Выполнен',
	);
	
	public static $users_types_titles = array(
		'natural_person' => 'ФизЛицо',
		'legal_person' => 'ЮрЛицо',
	);
	
	public static $delivery_types_titles = array(
		'pickup' => 'Самовывоз',
		'delivery' => 'Доставка',
		'ems' => 'EMS Почта России',
		'dpd' => 'Курьерская служба DPD',
		'boxberry' => 'Курьерская служба Boxberry',
	);
	
	public static $payment_types_titles = array(
		'cash_in_market' => 'В гипермаркете',
		'cash_to_driver' => 'Водителю наличными',
		'card_to_driver' => 'Водителю картой',
		'card' => 'Картой на сайте',
		'cashless' => 'По безналичному расчету',
		'yandex_money' => 'Яндекс деньги',
	);
	
	public static $lifting_types_titles = array(
		'' => 'Нет лифта',
		'passenger' => 'Пассажирский лифт',
		'service' => 'Грузовой лифт',
	);
	
	public static $root_url = '/';
	public static $url_base = '/';
	
	function url_base()
	{
		return self::$url_base;
	}
	
	function root_url()
	{
		return self::$root_url;
	}

	function link()
	{
		return $this->id;
	}
	
	function full_title()
	{
		$result = $this->get(__FUNCTION__);
		if (trim($result) === '') {
			$result = $this->title;
		}
		return $result;
	}
	
	function breadcrumbs($is_this_link = false) {
		$result = d()->Page->find_by_url(d()->url_to_system($this->root_url))->breadcrumbs(true);
		$result[] = array(
			'title' => $this->title,
			'link' => $is_this_link ? $this->url_base . $this->link : null,
		);
		return $result;
	}
	
	function show_status_id() {
		if (isset(self::$status_titles[(int) $this->status_id])) {
			return h(self::$status_titles[(int) $this->status_id]);
		}
		return '';
	}
	
	function products_price() {
		$result = 1 * $this->get(__FUNCTION__);
		if ($result < 1e-7) {
			$result = 0;
			foreach ($this->orders_items as $item) {
				$result += $item->number * $item->price;
			}
		}
		if ($this->get('delivery_type')!= ""){
			$delivery = d()->Delivery_variant->find_by_id($this->get('delivery_type'));
			if ($delivery->ne){
				if ($delivery->free_price != "" && $delivery->free_price*1 <= $result){
					return $result;
				}
				if ($delivery->price != "" && $delivery->price*1 >0){
					return ($result+$delivery->price);
				}
			}
		}
		return $result;
	}
	
	function order_price() {
		$result = 1 * $this->get(__FUNCTION__);
		if ($result < 1e-7) {
			$result = $this->products_price();
		}
		return $result;
	}
	
	function total_price() {
		return $this->products_price();
	}
	
	function price() {
		$result = $this->get(__FUNCTION__);
		if (1 * $result < 1e-7) {
			$result = $this->products_price();
		}
		return $result;
	}
	
	function number() {
		$result = 1 * $this->get(__FUNCTION__);
		if ($result < 1e-7) {
			$result = 0;
			foreach ($this->orders_items as $item) {
				$result += 1 * $item->number;
			}
		}
		return $result;
	}
	
	function total_weight() {
		$result = 1 * $this->get(__FUNCTION__);
		if ($result < 1e-7) {
			$result = 0;
			foreach ($this->orders_items as $item) {
				$result += $item->number * $item->weight;
			}
		}
		return $result;
	}
	
	function title() {
		$result = $this->get(__FUNCTION__);
		if (trim($result) === '') {
			$result = "Заказ #{$this->id}";
		}
		return $result;
	}
	
	function show_products_price() {
		$products_price = $this->products_price;
		return ($products_price > 1e-7) ? d()->price_format($products_price) : '';
	}
	
	function show_price() {
		$price = $this->price;
		return ($price > 1e-7) ? d()->price_format($price) : '';
	}
	
	function admin_list() {
		$this->where('`status_id` is not null');
		if (isset($_GET['sort_field'])) {
			$this->order('`' . et($_GET['sort_field']) . '`' . ((isset($_GET['sort_direction']) && ($_GET['sort_direction'] === 'desc')) ? ' desc' : ''));
		}
		return $this;
	}
	
	function lock_data() {
		foreach ($this->orders_items->all as $order_product) {
			$order_product->link        = $order_product->link;
			$order_product->title       = $order_product->title;
			$order_product->weight      = $order_product->weight;
			$order_product->image       = $order_product->image;
			$order_product->price       = $order_product->price;
			$order_product->total_price = $order_product->total_price;
			$order_product->sku_dealer       = $order_product->sku_dealer;
			$order_product->sku_nomenclature       = $order_product->sku_nomenclature; 
			$order_product->sku_producer        = $order_product->sku_producer;
			$order_product->save();
		}
		$this->products_price = $this->products_price;
		$this->payed_balls    = $this->payed_balls ;
		$this->order_price    = $this->order_price - $this->payed_balls;
		$this->save();
	}

	function show_delivery() {
		if(d()->Delivery_variant->find_by_id($this->delivery_type)->ne){
			return d()->Delivery_variant->find_by_id($this->delivery_type)->title;
		}
		return 'Неизвестно (возможно, произошла ошибка)';
	}
	function show_is_payed() {
		$result = "";
		switch ($this->is_paid) {
		case '1':
			$result .= '<span style="color:#3c3;font-weight:bold;font-size:1.5em;line-height:1em;">☑</span>';
			break;
		case '0':
			$result .= '<span style="color:#999;font-weight:bold;font-size:1.5em;line-height:1em;">☒</span>';
			break;
		case '2':
			$result .= '<span style="color:red;font-weight:bold;font-size:1.5em;line-height:1em;">☒</span>';
			break;
		}
		switch ($this->payment_type) {
		case 'cash':
			$result .= ' Наличными курьеру';
			break;
		case 'cashless':
			$result .= ' Безналичный расчет';
			break;
		case 'online':
			$result .= ' Онлайн';
			break;
		case 'PC':
			$result .= 'Оплата из кошелька в Яндекс.Деньгах (Онлайн яндекс.касса)';
			break;
		case 'AC':
			$result .= 'Оплата пластиковой картой (Онлайн яндекс.касса)';
			break;
		case 'SB':
			$result .= 'Оплата через Сбербанк: по смс или Сбербанк Онлайн (Онлайн яндекс.касса)';
			break;
		case 'GP':
			$result .= 'Оплата наличными через кассы и терминалы (Онлайн яндекс.касса)';
			break;
		case 'AB':
			$result .= 'Оплата через Альфа-Клик (Онлайн яндекс.касса)';
			break;
		case 'PB':
			$result .= 'Оплата через интернет-банк Промсвязьбанка (Онлайн яндекс.касса)';
			break;
		case 'bank':
			$result .= ' <b>Предварительная заявка</b> (Банковская квитанция)';
			break;
		case 'post':
			$result .= ' <b>Предварительная заявка</b> (Почтовый перевод)';
			break;
		case 'card':
			$result .= ' <b>Предварительная заявка</b> (Перечислением на карту)';
			break;
		case 'platron':
			$result .= ' Оплата через SMS (онлайн-оплата Platron)';
			break;
		}
		return $result;
	}

}
