<?php

d()->as_hnl2br = function($value, $field, $object) {
	return hnl2br($value);
};

d()->as_h = function($value, $field, $object) {
	return h($value);
};

d()->as_is = function($value, $field, $object) {
	return $value;
};

d()->as_model_title = function($value, $field, $object) {
	return $value->title;
};

d()->as_phone_href = function($value, $field, $object) {
	$value = trim(strtok($value, ','));
	if ($value !== '') {
		$value = 'tel:' . preg_replace(array('#^8#', '#[^0-9]#', '#^7#'), array('7', '', '+7'), $value);
	}
	return $value;
};


d()->as_admin_link_for = function($value, $field, $object) {
	if (iam()) {
		$object2 = $value;
		if (substr($field, -3) === '_id') {
			$object2 = $object[substr($field, 0, -3)];
		}
		if ($object2 instanceof ActiveRecord && $object->ne) {
			return '<a href="' . h(d()->url_for($object2)) . '" target="_blank">' . h($object2->title) . '</a>';
		}
	}
	return '';
};

d()->as_admin_link = function($value, $field, $object) {
	if (iam()) {
		return '<a href="' . h($value) . '" target="_blank">' . h($value) . '</a>';
	}
	return '';
};

d()->as_admin_check_mark = function($value, $field, $object) {
	if (iam()) {
		return $value
				? '<span style="color:#3c3;font-weight:bold;font-size:1.5em;line-height:1em;vertical-align:middle;">☑</span>'
				: '<span style="color:#c33;font-weight:bold;font-size:1.5em;line-height:1em;vertical-align:middle;">☒</span>';
	}
	return '';
};

d()->as_object_extended_title = function($value, $field, $object) {
	if (($name = cut_suffix($field, '_id')) !== false) {
		return $object[$name]['extended_title'];
	}
	return '';
};
