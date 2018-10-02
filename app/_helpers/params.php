<?php

d()->link_param_encode = function($arg) {
	return str_replace('%20', '+', urlencode($arg));
};
