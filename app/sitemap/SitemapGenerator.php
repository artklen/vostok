<?php

class SitemapGenerator {
	
	public $limit = 50000;
	public $max_size = 10485760; // 10Мб https://yandex.ru/support/webmaster/indexing-options/sitemap.xml
	public $empty_size = 282;
	public $files_names_template = 'sitemap';
	public $sitemap_index_name = 'sitemap';
	public $url_prefix;
	public $files_list;
	public $urls_list;
	public $current_size;
	
	function __construct() {
		$this->url_prefix = 'https://' . $_SERVER['HTTP_HOST'];
		$this->files_list = [];
		$this->urls_list = [];
		$this->current_size = $this->empty_size;
	}
	
	function add_url($url, $lastmod, $changefreq, $priority) {
		$str = '<url><loc>' . h($this->url_prefix . $url) . '</loc>';
		if ($lastmod !== '') {
			$str .= '<lastmod>' . $lastmod . '</lastmod>';
		}
		if ($changefreq !== '') {
			$str .= '<changefreq>' . $changefreq . '</changefreq>';
		}
		if ($priority !== '') {
			$str .= '<priority>' . $priority . '</priority>';
		}
		$str .= '</url>';
		if (count($this->urls_list) >= $this->limit || strlen($str) + $this->current_size > $this->max_size) {
			$this->save();
		}
		$this->urls_list[] = $str;
		$this->current_size += strlen($str);
	}
	
	function save() {
		if (!empty($this->urls_list)) {
			$file = '/sitemaps/' . $this->files_names_template . (count($this->files_list) + 1) . '.xml';
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . $file, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . implode($this->urls_list) . '</urlset>');
			$this->files_list[] = $file;
			$this->urls_list = [];
			$this->current_size = $this->empty_size;
		}
	}
	
	function generate_index() {
		$this->save();
		if (!empty($this->files_list)) {
			$lastmod = date('c');
			$file = '/sitemaps/' . $this->sitemap_index_name . '.xml';
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . $file, '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>' . h($this->url_prefix) . implode('</loc><lastmod>' . $lastmod . '</lastmod></sitemap><sitemap><loc>' . h($this->url_prefix), $this->files_list) . '</loc><lastmod>' . $lastmod . '</lastmod></sitemap></sitemapindex>');
		}
	}
	
}
