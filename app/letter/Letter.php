<?php

class Letter
{

	function render($name, $template = 'main')
	{
		d()->letter_content = '' . d()->view->render('/letter/' . $name . '.html');
		return '' . d()->view->render('/letter/templates/' . $template . '.html');
	}
	
	function __call($name, $args)
	{
		return $this->render($name, !empty($args) ? $args[0] : 'main');
	}
	
	function __get($name)
	{
		return $this->render($name);
	}
	
}
