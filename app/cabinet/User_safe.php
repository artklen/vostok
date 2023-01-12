<?php

class User_safe extends ActiveRecord
{

    public function save()
    {
        if (isset($_POST['regular_customer_products_discount_type'])) {
            switch ($_POST['regular_customer_products_discount_type']) {
                case 'auto':
                    $this->set('is_regular_customer_products_discount_manual', '0');
                    break;

                case 'on':
                    $this->set('is_regular_customer_products_discount_manual', '1');
                    $this->set('is_regular_customer_products_discount_manual_value', '1');
                    break;

                case 'off':
                    $this->set('is_regular_customer_products_discount_manual', '1');
                    $this->set('is_regular_customer_products_discount_manual_value', '0');
                    break;
            }
        }

        if (isset($_POST['password']['password']) && $_POST['password']['password'] !== '') {
            $this->set('password', d()->user_password_hash($_POST['password']['password']));
        }

        return parent::save();
    }
}