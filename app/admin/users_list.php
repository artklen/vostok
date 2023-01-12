<?php

d()->datapool['urls'][] = [
    '/admin/list/users',
    'admin_show_one_list_tpl',
    'admin_show_one_list_users_tpl',
];

function admin_show_one_list_users_tpl(): string
{
    return d()->View->partial('/admin/users_list.html');
}

function admin_users_list_source(array $params): User
{
    $search = (string) ($params['class'] ?? '');
    if ($search === '') {
        $sortField = $params['sort_field'] ?? 'sort';

        if (isset($params['sort_direction']) && $params['sort_direction'] === 'desc') {
            $sortDirection = 'desc';
        } else {
            $sortDirection = '';
        }

        return d()->User->order('`' . et($sortField) . '` ' . $sortDirection);
    }

    $result = d()->User;

    {
        $mask = e("%$search%");

        $hasDigits = preg_match('/\d/u', $search);
        $hasLetters = preg_match('/\pL/u', $search);
        if ($hasDigits && ! $hasLetters) {
            $digits = preg_replace('/\D/u', '', $search);
            if (strlen($digits) > 1 && ($digits[0] === '7' || $digits[0] === '8')) {
                $digits = substr($digits, 1);
            }
            $pattern = '"' . implode('[^0-9]*', str_split($digits)) . '"';
            $phoneCondition = "`phone` regexp $pattern";
        } else {
            $phoneCondition = "`phone` like $mask";
        }

        $result->where("`name` like $mask or `email` like $mask or $phoneCondition");
    }

    {
        $quoted = e($search);
        $byEquality = "coalesce(`name`, '')<>$quoted and coalesce(`email`, '')<>$quoted and coalesce(`phone`, '')<>$quoted";

        $mask = e("$search%");
        $bySameBegin = "coalesce(`name`, '') not like $mask and coalesce(`email`, '') not like $mask and coalesce(`phone`, '') not like $mask";

        $byDefault = '`id` desc';

        $result->order("$byEquality, $bySameBegin, $byDefault");
    }

    return $result;
}
