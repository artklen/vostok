<?php

/**
 * @property-read Orders_item orders_items
 *
 * @property string delivery_type
 * @property string delivery_cdek_point_city_title
 * @property string delivery_cdek_point_city_code
 * @property string delivery_cdek_point_code
 * @property string delivery_cdek_point_title
 * @property string delivery_cdek_point_address
 * @property string delivery_cdek_point_price
 * @property string delivery_cdek_point_delivery_working_days_min
 * @property string delivery_cdek_point_delivery_working_days_max
 * @property string delivery_cdek_courier_city_title
 * @property string delivery_cdek_courier_city_subtitle
 * @property string delivery_cdek_courier_city_code
 * @property string delivery_cdek_courier_city_fias
 * @property string delivery_cdek_courier_address
 * @property string delivery_cdek_courier_address_dadata
 * @property string delivery_cdek_courier_price
 * @property string delivery_cdek_courier_delivery_working_days_min
 * @property string delivery_cdek_courier_delivery_working_days_max
 * @property string delivery_post_address
 * @property string delivery_post_index
 * @property string delivery_post_address_dadata
 * @property string delivery_post_price
 * @property string delivery_price
 * @property string ordered_at
 * @property string status_id
 * @property string name
 * @property string phone
 * @property string address
 * @property string email
 * @property string comment
 * @property string pay_type
 * @property string secret
 * @property string errors
 * @property string session_key
 */
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
		$result = 1. * $this->get(__FUNCTION__);
		if ($result > 1e-7) {
            return $result;
        }

        $result = 0;
        /** @var Orders_item $item */
        foreach ($this->orders_items as $item) {
            $result += $item->total_price();
        }
        return $result;
    }
	
	function order_price() {
		$result = 1 * $this->get(__FUNCTION__);
		if ($result > 1e-7) {
            return $result;
        }

        return ($this->products_price() + (float) $this->delivery_price) * $this->payment_type_commission_coefficient();
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

    function lock_data()
    {
        /** @var Orders_item $order_product */
        foreach ($this->orders_items->all as $order_product) {
            $order_product->link = $order_product->link();
            $order_product->title = $order_product->title();
            $order_product->weight = $order_product->weight();
            $order_product->image = $order_product->image();
            $order_product->price = $order_product->price();
            $order_product->total_price = $order_product->total_price();
            $order_product->save();
        }
        $this->products_price = $this->products_price();
        $this->order_price = $this->order_price();
        $this->payment_type_commission_coefficient = $this->payment_type_commission_coefficient();
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
		return $result;
	}
	function show_pay_type() {
		$result = "";
		switch ($this->pay_type) {
		case PaymentType::ONLINE:
			$result .= 'Онлайн оплата';
			break;
		case PaymentType::COD:
			$result .= 'Наличными или перечислением';
			break;
		}
		return $result;
	}
	function pay_info() {
		$result = "";
		switch ($this->pay_type) {
		case PaymentType::ONLINE:
			$result .= 'Онлайн оплата';
			if ($this->is_paid){
				$result .= ' <span style="color:green">[Оплачено]</span>';
				if (strpos($this->chek_response, '{"Error":0}')){
					$result .= ' <span style="color:green">(Чек отправлен)</span>';
				}else{
					$result .= ' <span style="color:red">(Ошибка отправки чека, обратитесь к администратору)</span>';
				}
			}
			break;
		case PaymentType::COD:
			$result .= 'Наличными или перечислением';
			break;
		}
		return $result;
	}

    public function payment_type_commission_coefficient(): float
    {
        $storedValue = $this->get(__FUNCTION__);
        if ($storedValue !== '') {
            return (float) $storedValue;
        }

        if ($this->pay_type === PaymentType::COD) {
            switch ($this->delivery_type) {
                default:
                    return 1.;

                case DeliveryType::POST:
                case DeliveryType::EMS:
                    return 1.02;

                case DeliveryType::CDEK_POINT:
                case DeliveryType::CDEK_COURIER:
                    return 1.03;
            }
        }

        return 1.;
    }
}