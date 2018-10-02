<?php

d()->simple_ajax_form = function($params) {
	return d()->form($params + [
		'action' => $params[0],
		'ajax' => true,
		'simple_names' => true,
	]);
};

d()->return_ajax_notice = function() {
	print '_current_form.find(".js-notice").html(' . json_encode(d()->notice(['style' => '', 'class' => 'alert alert-danger'])) . ');';
	exit;
};

d()->unique_input_id = function() {
	static $i = 0;
	return 'input-' . ++$i . '-' . d()->form_type . '-' . 1 * AJAX;
};
