<?php

/**
 * @property string name
 * @property string email
 * @property string phone
 * @property string secret
 * @property string password
 * @property string is_regular_customer_products_discount_manual
 * @property string is_regular_customer_products_discount_manual_value
 */
class User extends ActiveRecord
{
    public function secret()
    {
        $result = $this->get(__FUNCTION__);
        if ($result !== '') {
            return $result;
        }

        $reloaded = (new self())->f($this->id);
        $result = $reloaded->get(__FUNCTION__);
        if ($result !== '') {
            return $result;
        }

        $result = md5(uniqid(mt_rand() . json_encode($_SERVER) . session_id(), true));

        $persisted = (new self())->f($this->id);
        $persisted->secret = $result;
        $persisted->save();

        return $result;
    }

    public function addresses(): Addres
    {
        if ($this->is_empty()) {
            return d()->Addres->stub();
        }
        return d()->Addres->find_by('user_id', $this->id)->order_by('`id` desc');
    }

    public function orders(): Order
    {
        if ($this->is_empty()) {
            return d()->Order->stub();
        }
        return d()->Order
            ->where('`user_id`=?', $this->id)
            ->where('`status_id` is not null')
            ->order_by('`id` desc');
    }

    public function is_regular_customer_products_discount(): bool
    {
        if ($this->is_regular_customer_products_discount_manual) {
            return (bool) $this->is_regular_customer_products_discount_manual_value;
        }

        return $this->is_regular_customer_products_discount_auto_value();
    }

    public function is_regular_customer_products_discount_auto_value(): bool
    {
        if ($this->is_empty()) {
            return false;
        }

        return d()->Order
            ->where('`user_id`=?', $this->id)
            ->where('`status_id`=?', Order::COMPLETE)
            ->ne();
    }
}
