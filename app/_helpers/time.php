<?php

d()->time = static function () {
    static $time;
    if (! isset($time)) {
        $time = time();
    }
    return $time;
};

d()->time_format = static function ($time, $format = null) {
    if (is_array($time)) {
        $args = $time;
        $time = array_shift($args);
        $format = array_shift($args);
    }

    if (is_array($format)) {
        $format = reset($format);
    }

    return (string) date($format, strtotime($time));
};

d()->now = static function () {
    static $result;
    if (! isset($result)) {
        $result = date('Y-m-d H:i:s', d()->time);
    }
    return $result;
};

d()->now_date = static function () {
    static $result;
    if (! isset($result)) {
        $result = date('Y-m-d', d()->time);
    }
    return $result;
};

d()->now_year = static function () {
    static $result;
    if (! isset($result)) {
        $result = date('Y', d()->time);
    }
    return $result;
};

d()->zero_time = '0000-00-00 00:00:00';

/** @see doitClass::is_empty_time() */
d()->is_empty_time = static function (string $time): bool {
    return $time === '' || $time === d()->zero_time;
};