<?php

/**
 * @property string is_paid
 * @property string is_canceled
 * @property string order_id
 * @property string sum
 * @property string is_awaiting
 * @property string sber_url
 * @property string sber_order_code
 * @property string full_status
 * @property string yookassa_code
 * @property string yookassa_link
 * @property string yookassa_status
 */
class Payment extends ActiveRecord
{
    public const SBER_TYPE = 'sber';
    public const YOOKASSA_TYPE = 'yookassa';
}