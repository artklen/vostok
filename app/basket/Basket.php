<?php

class Basket extends UniversalSingletoneHelper
{
	/** @var Order */
	public $order = null;

	protected function init_order()
	{
		$order = d()->Order->where('`session_key`=? and `status_id` is null', session_id())->order('`created_at` desc')->limit(1);
		if ($order->ne()) {
			$this->order = $order;
		}
	}
	
	function current()
	{
		if (! isset($this->order)) {
			$this->init_order();
		}

		return $this;
	}
	
	function create_order_if_not_exists()
	{
        if (isset($this->order)) {
            return;
        }

        /** @var Order $order */
        $order = d()->Order->new;
        $order->session_key = session_id();
        $order->save();
        $this->order = d()->Order->find_by('id', $order->insert_id);
    }

	function add_item($params, $number = null)
	{
		$item_key = $this->item_key_of($params);
		if (!isset($number)) {
			$number = $this->get_number_from($params);
		}
		if (!$number) {
			$number = 1;
		}
		$item = $this->find_or_create_item($params);
		$item->number = $item->number + $number;
		$item->save();
	}
	
	function update_item($params, $number = null)
	{
		$item_key = $this->item_key_of($params);
		if (!isset($number)) {
			$number = $this->get_number_from($params);
		}
		if (1 * $number < 1e-7) {
			//$this->delete_item($params);
			$number = 1;
		}
		$item = $this->find_or_create_item($params);
		$item->number = $number;
		$item->save();
	}
	
	function delete_item($params)
	{
		$item = $this->find_item($this->item_key_of($params));
		if ($item->ne) {
			$item->delete();
		}
	}
	
	function find_or_create_item($params)
	{
		$result = $this->find_item($params);
		if ($result->is_empty) {
			$result = $this->create_item($params);
		}
		return $result;
	}
	
	function orders_items()
	{
		if (isset($this->order)) {
			return d()->Orders_item->where('`order_id`=?', $this->order->id);
		}
		return d()->Orders_item->where('false');
	}
	
	function find_item($item_key)
	{
		if ($item_key instanceof Orders_item) {
			return $item_key;
		}
		if (is_array($item_key) || is_object($item_key)) {
			$item_key = $this->item_key_of($item_key);
		}
		return $this->orders_items->where('`item_key`=?', $item_key);
	}
	
	function create_item($params)
	{
		static $white_list = array('product_id', 'products_variant_id');
		$this->create_order_if_not_exists();
		$item_key = $this->item_key_of($params);
		$arr = array(
			'item_key' => $item_key,
			'order_id' => $this->order->id,
		);
		if (is_object($params)) {
			$is_ar = $params instanceof ActiveRecord;
			foreach ($white_list as $key) {
				if ($is_ar || isset($params->$key) || property_exists($params, $key)) {
					$arr[$key] = $params->$key;
				}
			}
		} else {
			foreach ($white_list as $key) {
				if (isset($params[$key])) {
					$arr[$key] = $params[$key];
				}
			}
		}
		return d()->Orders_item->find_by_id(d()->Orders_item->create($arr)->insert_id);
	}
	
	function get_number_from($params)
	{
		if (is_array($params)) {
			if (isset($params['number'])) {
				return (int) $params['number'];
			}
		} else if (is_object($params)) {
			if (($params instanceof ActiveRecord) || isset($params->number) || property_exists($params, 'number')) {
				return (int) $params->number;
			}
		}
		return 0;
	}
	
	function clear()
	{
		if (isset($this->order)) {
			d()->db->exec('delete from `orders_items` where `order_id`=' . d()->db->quote($this->order->id));
		}
	}
		
	function total_number($item_key = null)
	{
		$result = 0;
		if (is_array($item_key) || is_object($item_key)) {
			$item_key = $this->item_key_of($item_key);
		}
		$items = $this->items($item_key);
		foreach ($items as $item) {
			$result += $items->number;
		}
		return $result;
	}

	function total_price($item_key = null)
	{
	    if (! isset($item_key)) {
            return $this->order_price();
        }

	    $result = 0;
        /** @var Orders_item $item */
        foreach ($this->items($item_key) as $item) {
            $result += $item->total_price();
        }
        return $result;
	}
	
	function products_price()
	{
	    $result = 0;
	    /** @var Orders_item $item */
        foreach ($this->orders_items() as $item) {
            $result += $item->total_price();
        }
        return $result;
	}
	
	function order_price()
	{
	    if (! isset($this->order)) {
	        return 0.;
        }

        return ($this->products_price() + $this->delivery_price()) * $this->order->payment_type_commission_coefficient();
	}

	function total_weight($item_key = null)
	{
		$result = 0;
		if (is_array($item_key) || is_object($item_key)) {
			$item_key = $this->item_key_of($item_key);
		}
		$items = $this->items($item_key);
		foreach ($items as $item) {
			$result += $item->number * $item->weight;
		}
		return $result;
	}
	
	function product_total_number($product_id)
	{
		$result = 0;
		$product_items = $this->product_items($product_id);
		foreach ($product_items as $item) {
			$result += $item->number;
		}
		return $result;
	}
	
	function product_total_price($product_id)
	{
		$result = 0;
		$product_items = $this->product_items($product_id);
		foreach ($product_items as $item) {
			$result += $item->number * $item->price;
		}
		return $result;
	}
	
	function product_total_weight($product_id)
	{
		$result = 0;
		$product_items = $this->product_items($product_id);
		foreach ($product_items as $item) {
			$result += $item->number * $item->weight;
		}
		return $result;
	}
	
	function items($item_key = null) {
        if (! isset($item_key)) {
            return $this->orders_items();
        }

        if (is_array($item_key) || is_object($item_key)) {
            $item_key = $this->item_key_of($item_key);
        }
        return $this->orders_items()->where('`item_key`=?', $item_key);
	}
	
	function product_items($product_id) {
		if (is_array($product_id)) {
			$product_id = $product_id['id'];
		} else if (is_object($product_id)) {
			$product_id = $product_id->id;
		}
		return $this->orders_items()->where('`product_id`=?', $product_id);
	}
	
	function products_items($products) {
		$ids = array();
		foreach ($products as $product) {
			if (is_array($product)) {
				$id = $product['id'];
			} else if (is_object($product)) {
				$id = $product->id;
			} else {
				$id = "$product";
			}
			$ids[$id] = $id;
		}
		return $this->orders_items()->where('`product_id` in (?)', $ids);
	}
	
	public function item_keys() {
		return $this->items->fast_all_of('item_key');
	}
	
	/**
	 * Определения уникального ключа, идентифицирующего товар в корзине
	 */
	function item_key_of($params) {
		if ($params instanceof Orders_item) {
			return $params['item_key'];
		}
		$result = '';
		if (isset($params['product_id']) && $params['product_id'] !== '') {
			$result .= 'p' . $params['product_id'];
		}
		if (isset($params['products_variant_id']) && $params['products_variant_id'] !== '') {
			$result .= 'v' . $params['products_variant_id'];
		}
		return $result;
	}

	/** array<Delivery_variant> */
    public function delivery_variants(): array
    {
        return d()->Delivery_variant->all();
	}

    public function delivery_type(): string
    {
        return $this->order->delivery_type ?? '';
	}

    public function delivery_variant(): Delivery_variant
    {
        $delivery_type = $this->delivery_type();
        if ($delivery_type === '') {
            return d()->Delivery_variant->where('false');
        }

        return (new DeliveryType($delivery_type))->variant();
    }

    public function set_delivery_type(array $params): void
    {
        $this->create_order_if_not_exists();

        $this->order->delivery_type = $params['delivery_type'];

        $this->order = $this->order->save_and_load();
	}

    public function set_delivery_cdek_point_city(array $params): void
    {
        $this->create_order_if_not_exists();

        if ($this->order->delivery_cdek_point_city_code === $params['code']) {
            return;
        }

        $this->order->delivery_cdek_point_city_title = $params['title'];
        $this->order->delivery_cdek_point_city_code = $params['code'];

        $tariffs = d()->Cdek->pointTariff((int) $params['code'], $this->products_price());
        $tariff = $this->get_cdek_tariff_with_smallest_sum($tariffs);
        if (isset($tariff)) {
            $this->order->delivery_cdek_point_price = $tariff->sum;
            $this->order->delivery_cdek_point_delivery_working_days_min = $tariff->deliveryWorkingDaysMin;
            $this->order->delivery_cdek_point_delivery_working_days_max = $tariff->deliveryWorkingDaysMax;
        } else {
            $this->order->delivery_cdek_point_price = '';
            $this->order->delivery_cdek_point_delivery_working_days_min = '';
            $this->order->delivery_cdek_point_delivery_working_days_max = '';
        }

        $this->order->delivery_cdek_point_title = '';
        $this->order->delivery_cdek_point_code = '';

        $this->order = $this->order->save_and_load();
    }

    public function set_delivery_cdek_point(array $params): void
    {
        $this->create_order_if_not_exists();

        $this->order->delivery_cdek_point_title = $params['title'];
        $this->order->delivery_cdek_point_code = $params['code'];

        $this->order = $this->order->save_and_load();
    }

    public function delivery_cdek_point_city_title(): string
    {
        return $this->order->delivery_cdek_point_city_title ?? '';
    }

    public function delivery_cdek_point_city_code(): string
    {
        return $this->order->delivery_cdek_point_city_code ?? '';
    }

    public function delivery_cdek_point_code(): string
    {
        return $this->order->delivery_cdek_point_code ?? '';
    }

    public function delivery_cdek_point_title(): string
    {
        return $this->order->delivery_cdek_point_title ?? '';
    }

    public function delivery_price(): float
    {
        if (! isset($this->order)) {
            return 0.;
        }

        if ($this->is_free_delivery()) {
            return 0.;
        }

        $variant_price = $this->delivery_variant_price();

        switch ($this->delivery_type()) {
            default:
                return $variant_price;

            case DeliveryType::CDEK_POINT:
                return (float) ($this->order->delivery_cdek_point_price ?? 0.);

            case DeliveryType::CDEK_COURIER:
                return (float) ($this->order->delivery_cdek_courier_price ?? 0.);

            case DeliveryType::POST:
                return (float) ($this->order->delivery_post_price ?? 0.);
        }
    }

    public function delivery_working_days_description(): string
    {
        $min = $this->delivery_working_days_min();
        $max = $this->delivery_working_days_max();

        if ($min === $max && $min !== 0) {
            return $min . ' ' . t(declOfNum($min, 'рабочий день', 'рабочих дня', 'рабочих дней'));
        }

        if ($min !== 0) {
            $parts[] = t('от') . ' ' . $min;
        }
        if ($max !== 0) {
            $parts[] = t('до') . ' ' . $max;
        }

        if (! isset($parts)) {
            return '';
        }

        return implode(' ', $parts) . ' ' . t('рабочих дней');
    }

    public function delivery_working_days_min(): int
    {
        switch ($this->delivery_type()) {
            default:
                return 0;

            case DeliveryType::CDEK_POINT:
                return (int) $this->order->delivery_cdek_point_delivery_working_days_min;

            case DeliveryType::CDEK_COURIER:
                return (int) $this->order->delivery_cdek_courier_delivery_working_days_min;
        }
    }

    public function delivery_working_days_max(): int
    {
        switch ($this->delivery_type()) {
            default:
                return 0;

            case DeliveryType::CDEK_POINT:
                return (int) $this->order->delivery_cdek_point_delivery_working_days_max;

            case DeliveryType::CDEK_COURIER:
                return (int) $this->order->delivery_cdek_courier_delivery_working_days_max;
        }
    }

    public function is_free_delivery(): bool
    {
        $variant = $this->delivery_variant();
        if ($variant->is_empty()) {
            return false;
        }

        if ($variant->free_price === '') {
            return false;
        }

        return $this->products_price() > (float) $variant->free_price - 1e-7;
    }

    private function delivery_variant_price(): float
    {
        $variant = $this->delivery_variant();
        if ($variant->is_empty()) {
            return 0.;
        }

        return (float) $variant->price;
    }

    public function calculate_delivery_price(): float
    {
        if (! isset($this->order)) {
            return 0.;
        }

        if ($this->is_free_delivery()) {
            return 0.;
        }

        switch ($this->delivery_type()) {
            default:
                return $this->delivery_variant_price();

            case DeliveryType::CDEK_POINT:
                $tariffs = d()->Cdek->pointTariff(
                    (int) $this->order->delivery_cdek_point_city_code,
                    $this->products_price()
                );
                $tariff = $this->get_cdek_tariff_with_smallest_sum($tariffs);
                return $tariff->sum ?? 0.;

            case DeliveryType::CDEK_COURIER:
                $tariffs = d()->Cdek->courierTariff(
                    (int) $this->order->delivery_cdek_courier_city_code,
                    $this->products_price()
                );
                $tariff = $this->get_cdek_tariff_with_smallest_sum($tariffs);
                return $tariff->sum ?? 0.;

            case DeliveryType::POST:
                return d()->RussianPost->cost($this->order->delivery_post_index, $this->products_price());
        }
    }

    private function get_cdek_tariff_with_smallest_sum(array $tariffs): ?CdekTariff
    {
        if (empty($tariffs)) {
            return null;
        }

        usort(
            $tariffs,
            static function (CdekTariff $a, CdekTariff $b): int {
                return $a->sum <=> $b->sum;
            }
        );

        return reset($tariffs);
    }

    public function delivery_cdek_courier_city(): Cdek_city
    {
        if (! isset($this->order)) {
            return d()->Cdek_city->where('false');
        }

        return d()->Cdek_city->f($this->order->delivery_cdek_courier_city_code);
    }

    public function set_delivery_cdek_courier_city(array $params): void
    {
        $this->create_order_if_not_exists();

        if ($this->order->delivery_cdek_courier_city_code === $params['code']) {
            return;
        }

        $this->order->delivery_cdek_courier_city_code = $params['code'];

        $tariffs = d()->Cdek->courierTariff((int) $params['code'], $this->products_price());
        $tariff = $this->get_cdek_tariff_with_smallest_sum($tariffs);
        if (isset($tariff)) {
            $this->order->delivery_cdek_courier_price = $tariff->sum;
            $this->order->delivery_cdek_courier_delivery_working_days_min = $tariff->deliveryWorkingDaysMin;
            $this->order->delivery_cdek_courier_delivery_working_days_max = $tariff->deliveryWorkingDaysMax;
        } else {
            $this->order->delivery_cdek_courier_price = '';
            $this->order->delivery_cdek_courier_delivery_working_days_min = '';
            $this->order->delivery_cdek_courier_delivery_working_days_max = '';
        }

        $this->clear_delivery_cdek_courier_address();

        $this->order = $this->order->save_and_load();
    }

    private function clear_delivery_cdek_courier_address(): void
    {
        $this->order->delivery_cdek_courier_address = '';
        $this->order->delivery_cdek_courier_address_dadata = '';
    }

    public function delivery_cdek_courier_address_dadata(): string
    {
        if (! isset($this->order) || $this->order->delivery_cdek_courier_address_dadata === '') {
            return '{}';
        }
        return $this->order->delivery_cdek_courier_address_dadata;
    }

    public function delivery_cdek_courier_address(): string
    {
        return $this->order->delivery_cdek_courier_address ?? '';
    }

    public function set_delivery_cdek_courier_address($params): void
    {
        $this->create_order_if_not_exists();

        $this->order->delivery_cdek_courier_address = $params['address'] ?? '';
        $this->order->delivery_cdek_courier_address_dadata = $params['dadata'] ?? '';

        $this->order = $this->order->save_and_load();
    }

    public function set_delivery_post_address($params): void
    {
        $this->create_order_if_not_exists();

        $this->order->delivery_post_address = $params['address'] ?? '';
        $this->order->delivery_post_index = $params['index'] ?? '';
        $this->order->delivery_post_address_dadata = $params['dadata'] ?? '';

        $this->order->delivery_post_price = d()->RussianPost->cost($params['index'] ?? '', $this->products_price());

        $this->order = $this->order->save_and_load();
    }

    public function delivery_post_address(): string
    {
        return $this->order->delivery_post_address ?? '';
    }

    public function errors(): array
    {
        if ($this->isCdekPointDeliveryCostInvalid()) {
            $errors[] = t('Не удалось рассчитать стоимость доставки до пункта выдачи СДЭК');
        }

        if ($this->isCdekCourierDeliveryCostInvalid()) {
            $errors[] = t('Не удалось рассчитать стоимость доставки курьером СДЭК');
        }

        if ($this->isPostDeliveryCostInvalid()) {
            $errors[] = t('Не удалось рассчитать стоимость доставки Почтой России');
        }

        return $errors ?? [];
    }

    public function errors_html(): string
    {
        $errors = $this->errors();
        if (empty($errors)) {
            return '';
        }

        return '<ul class="alert alert-danger"><li>' . implode('</li><li>', array_map('h', $errors)) . '</li></ul>';
    }

    private function isCdekPointDeliveryCostInvalid(): bool
    {
        if ($this->delivery_type() !== DeliveryType::CDEK_POINT) {
            return false;
        }

        if ($this->is_free_delivery()) {
            return false;
        }

        if ($this->delivery_cdek_point_city_code() === '') {
            return false;
        }

        return ((float) ($this->order->delivery_cdek_point_price ?? 0.)) < 1e-7;
    }

    private function isCdekCourierDeliveryCostInvalid(): bool
    {
        if ($this->delivery_type() !== DeliveryType::CDEK_COURIER) {
            return false;
        }

        if ($this->is_free_delivery()) {
            return false;
        }

        if ($this->delivery_cdek_courier_city()->is_empty()) {
            return false;
        }

        return ((float) ($this->order->delivery_cdek_courier_price ?? 0.)) < 1e-7;
    }

    private function isPostDeliveryCostInvalid(): bool
    {
        if ($this->delivery_type() !== DeliveryType::POST) {
            return false;
        }

        if ($this->is_free_delivery()) {
            return false;
        }

        if ($this->delivery_post_address() === '') {
            return false;
        }

        return ((float) ($this->order->delivery_post_price ?? 0.)) < 1e-7;
    }

    public function validate_delivery(): bool
    {
        switch ($this->delivery_type()) {
            default:
                d()->add_notice('Выберите способ доставки', 'delivery_type');
                return false;

            case DeliveryType::PICKUP:
                return true;

            case DeliveryType::EMS:
                if ($this->order->address === '') {
                    d()->add_notice('Укажите адрес доставки', 'address');
                    return false;
                }
                return true;

            case DeliveryType::POST:
                if ($this->delivery_post_address() === '') {
                    d()->add_notice('Укажите адрес доставки', 'delivery_type');
                    return false;
                }

                return true;

            case DeliveryType::CDEK_POINT:
                if ($this->delivery_cdek_point_city_code() === '') {
                    d()->add_notice('Укажите город доставки', 'delivery_type');
                    return false;
                }

                if ($this->delivery_cdek_point_code() === '') {
                    d()->add_notice('Укажите пункт выдачи', 'delivery_type');
                    return false;
                }

                return true;

            case DeliveryType::CDEK_COURIER:
                if ($this->delivery_cdek_courier_city()->is_empty()) {
                    d()->add_notice('Укажите город доставки', 'delivery_type');
                    return false;
                }

                if ($this->delivery_cdek_courier_address() === '') {
                    d()->add_notice('Укажите адрес доставки', 'delivery_type');
                    return false;
                }

                return true;
        }
    }

    public function lock_delivery_data(): void
    {
        if ($this->delivery_type() === DeliveryType::CDEK_COURIER) {
            $city = $this->delivery_cdek_courier_city();
            $this->order->delivery_cdek_courier_city_title = $city->title;
            $this->order->delivery_cdek_courier_city_subtitle = $city->subtitle;
            $this->order->delivery_cdek_courier_city_fias = $city->fias;
        }
    }

    public function clear_irrelevant_delivery_data(): void
    {
        switch ($this->delivery_type()) {
            default:
                $this->clear_post_delivery_data();
                $this->clear_cdek_point_delivery_data();
                $this->clear_cdek_courier_delivery_data();
                return;

            case DeliveryType::POST:
                $this->clear_cdek_point_delivery_data();
                $this->clear_cdek_courier_delivery_data();
                return;

            case DeliveryType::CDEK_POINT:
                $this->clear_post_delivery_data();
                $this->clear_cdek_courier_delivery_data();
                return;

            case DeliveryType::CDEK_COURIER:
                $this->clear_post_delivery_data();
                $this->clear_cdek_point_delivery_data();
                return;
        }
    }

    private function clear_post_delivery_data(): void
    {
        $this->order->delivery_post_address = '';
        $this->order->delivery_post_index = '';
        $this->order->delivery_post_address_dadata = '';
        $this->order->delivery_post_price = '';
    }

    private function clear_cdek_point_delivery_data(): void
    {
        $this->order->delivery_cdek_point_city_title = '';
        $this->order->delivery_cdek_point_city_code = '';
        $this->order->delivery_cdek_point_code = '';
        $this->order->delivery_cdek_point_title = '';
        $this->order->delivery_cdek_point_price = '';
    }

    private function clear_cdek_courier_delivery_data(): void
    {
        $this->order->delivery_cdek_courier_city_title = '';
        $this->order->delivery_cdek_courier_city_subtitle = '';
        $this->order->delivery_cdek_courier_city_code = '';
        $this->order->delivery_cdek_courier_city_fias = '';
        $this->order->delivery_cdek_courier_address = '';
        $this->order->delivery_cdek_courier_address_dadata = '';
        $this->order->delivery_cdek_courier_price = '';
    }

    public function cash_on_delivery_title(): string
    {
        switch ($this->delivery_type()) {
            default:
                return t('Оплата при получении');

            case DeliveryType::POST:
            case DeliveryType::EMS:
                return t('Наложенный платеж (+ 2% комиссия)');

            case DeliveryType::CDEK_POINT:
            case DeliveryType::CDEK_COURIER:
                return t('Наложенный платеж (+ 3% комиссия)');
        }
    }

    public function set_pay_type($params): void
    {
        $this->create_order_if_not_exists();

        $this->order->pay_type = $params['pay_type'];

        $this->order = $this->order->save_and_load();
    }

    public function pay_type(): string
    {
        return $this->order->pay_type ?? '';
    }
}