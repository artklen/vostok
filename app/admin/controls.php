<?php

$dir = __DIR__ . '/controls';
if (false !== ($handle = opendir($dir))) {
	while (false !== ($file = readdir($handle))) {
		if (is_file($dir . '/' . $file) && (strrchr($file, '.') === '.html')) {
			$name = substr($file, 0, -5);
			d()->{"admin_$name"} = function() use ($name) {
				print '' . d()->view->render('/admin/controls/' . $name . '.html');
			};
		}
	}
}
