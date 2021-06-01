<?php

d()->simple_ajax_form = static function($params) {
	return d()->form($params + [
		'action' => $params[0],
		'ajax' => true,
		'simple_names' => true,
	]);
};

d()->ajax_notice = static function() {
	return '_current_form.find(".js-notice").html(' . json_encode(d()->notice(['style' => '', 'class' => 'alert alert-danger'])) . ');';
};

d()->return_ajax_notice = static function() {
	print d()->ajax_notice();
	exit;
};

d()->unique_input_id = static function() {
	static $i = 0;
	return 'input-' . ++$i . '-' . d()->form_type . '-' . 1 * AJAX;
};
