<?php defined('SYSPATH') or die('No direct script access.');

if ( ! defined('KOHANA_START_TIME'))
{
	/**
	 * Define the start time of the application, used for profiling.
	 */
	define('KOHANA_START_TIME', microtime(TRUE));
}

if ( ! defined('KOHANA_START_MEMORY'))
{
	/**
	 * Define the memory usage at the start of the application, used for profiling.
	 */
	define('KOHANA_START_MEMORY', memory_get_usage());
}

/**
 * Just an alias of JText
 */
function __($string, array $values = NULL, $lang = 'en-us')
{
	return JText::_($string);
}
