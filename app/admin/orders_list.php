<?php

d()->datapool['urls'][] = [
    '/admin/list/orders',
    'admin_show_one_list_tpl',
    'admin_show_one_list_orders_tpl',
];

function admin_show_one_list_orders_tpl(): string
{
    return d()->View->partial('/admin/orders_list.html');
}

function admin_orders_list_source(array $params): Order
{
    $result = d()->Order->where('`status_id` is not null');

    $search = (string) ($params['class'] ?? '');
    if ($search === '') {
        if (isset($params['sort_field'])) {
            $sortField = $params['sort_field'];
            $sortExpression = '`' . et($sortField) . '`';
            if ($sortField === 'order_price' || $sortField === 'products_price') {
                $sortExpression = "1*$sortExpression";
            }

            if (isset($params['sort_direction']) && $params['sort_direction'] === 'desc') {
                $sortDirection = 'desc';
            } else {
                $sortDirection = '';
            }

            $result->order("$sortExpression $sortDirection");
        } else {
            $result->order('`ordered_at` desc');
        }

        return $result;
    }

    $mask = e("%$search%");
    $result = d()->Order->where("`id` like $mask or `name` like $mask");

    {
        $quoted = e($search);
        $byEquality = "`id`<>$quoted and coalesce(`name`, '')<>$quoted";

        $mask = e("$search%");
        $bySameBegin = "`id` not like $mask and coalesce(`name`, '') not like $mask";

        $byDefault = '`ordered_at` desc';

        $result->order("$byEquality, $bySameBegin, $byDefault");
    }

    return $result;
}

function as_order_status_title($value): string
{
    return Order::$status_titles[$value] ?? '';
}
