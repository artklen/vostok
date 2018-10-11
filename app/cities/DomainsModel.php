<?php

trait DomainsModel
{
	
	function domain()
	{
		$result = $_ENV['SITE_MAIN_DOMAIN'];
		if ($this->subdomain !== '') {
			$result = $this->subdomain . '.' . $result;
		}
		return $result;
	}
	
}
