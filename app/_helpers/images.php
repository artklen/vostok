<?php

function full_preview($arg) {
	if (is_array($arg)) {
		$arg = reset($arg);
	}
	return d()->preview([$arg, 'in1000', 'in1000', 'not_resize' => true]);
}
