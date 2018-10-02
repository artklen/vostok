<?php

function sbs($haystack, $needle)
{
	return substr($haystack, 0, strlen($needle)) === $needle;
}

function ses($haystack, $needle)
{
	return substr($haystack, -strlen($needle)) === $needle;
}

function cut_prefix($haystack, $needle)
{
	return sbs($haystack, $needle) ? substr($haystack, strlen($needle)) : false;
}

function cut_suffix($haystack, $needle)
{
	return ses($haystack, $needle) ? substr($haystack, 0, -strlen($needle)) : false;
}
