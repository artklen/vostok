<?php

/**
 * @property-read Delivery_variant Delivery_variant
 * @property-read View View
 * @property-read Orders_item Orders_item
 * @property-read CdekCity CdekCity
 * @property-read CdekPoint CdekPoint
 * @property-read Cdek Cdek
 * @property-read CdekCitiesLoader CdekCitiesLoader
 * @property-read Cdek_city Cdek_city
 * @property-read Option Option
 * @property-read RussianPost RussianPost
 * @property-read Order Order
 *
 * @property string langlink
 * @property View view
 * @property Basket basket
 * @property Delivery_variant delivery_variant
 * @property Cdek_city cdek_city
 * @property string url_path
 * @property array params
 * @property Notification notification
 * @property string form_type
 * @property Order order
 *
 * @property callable basket_ajax_refresh
 * @method string basket_ajax_refresh()
 *
 * @property callable time
 * @method int time()
 *
 * @property callable ajax_notice
 * @method string ajax_notice()
 *
 * @property callable return_ajax_notice
 * @method void return_ajax_notice()
 *
 * @property callable simple_ajax_form
 * @method string simple_ajax_form(array $params)
 *
 * @method string form(array $params)
 *
 * @property callable unique_input_id
 * @method string unique_input_id()
 */
class doitClass
{
}

/**
 * @property string id
 * @property string sort
 * @property string created_at
 * @property string updated_at
 * @property string url
 * @property string text
 * @property string title
 * @property string type
 * @property-read ActiveRecord new
 * @property-read bool ne
 * @property-read bool is_empty
 * @property-read array all
 * @property-read int count
 * @property-read string ioi
 * @property-read array to_array
 * @property-read string to_json
 * @property array _data
 *
 * @method __construct(array $options = [])
 */
class ActiveRecord {
}

/**
 * @property string free_price
 * @property string price
 */
class  Delivery_variant extends ActiveRecord
{
}

/**
 * @property string subtitle
 * @property string fias
 */
class Cdek_city extends ActiveRecord
{
}

/**
 * @property string delivery_package_weight
 * @property string delivery_package_length
 * @property string delivery_package_width
 * @property string delivery_package_height
 * @property string delivery_from_post_index
 */
class Option extends ActiveRecord
{
}
