<?php

d()->custom_pagination = function($list = null, $template_path = null) {
	if (is_array($list)) {
		list($list, $template_path) = $list;
	} else if (is_array($template_path)) {
		$template_path = $template_path[0];
	}
	return d()->Paginator->custom_template($template_path)->generate($list);
};
