<?php
	# I'm sorry, Damir, but I needed to see it, so I made a small patch for myself
	#if (file_exists($injection = __DIR__.'/medicaments/index.php')) {require_once $injection;return;}
	#if(!isset($_GET['nr'])){
	#	if ((!isset($_SERVER['HTTP_IS_HTTPS']) || $_SERVER['HTTP_IS_HTTPS'] !== 'on') && $_SERVER['REQUEST_URI'] !== '/robots.txt' && (!isset($_SERVER['IS_HTTPS']) || $_SERVER['IS_HTTPS'] !== 'on')) {
	#		$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	#		header('HTTP/1.1 301 Moved Permanently');
	#		header("Location: $redirect");
	#		exit;
	#	}
	#}
	include_once ('config.php');

	$start_time = microtime(true);
	include_once ('cms/cms.php');

	header('Content-type: text/html; Charset=UTF-8');

	$result =  d()->main();

	$exec_time = microtime(true) - $start_time;
	header("X-CMS-Runtime: {$exec_time}s, ". memory_get_usage(true).'b');
	print $result;