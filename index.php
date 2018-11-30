<?php
	# I'm sorry, Damir, but I needed to see it, so I made a small patch for myself
	#if (file_exists($injection = __DIR__.'/medicaments/index.php')) {require_once $injection;return;}

	include_once ('config.php');

	$start_time = microtime(true);
	include_once ('cms/cms.php');

	header('Content-type: text/html; Charset=UTF-8');

	$result =  d()->main();

	$exec_time = microtime(true) - $start_time;
	header("X-CMS-Runtime: {$exec_time}s, ". memory_get_usage(true).'b');
	print $result;