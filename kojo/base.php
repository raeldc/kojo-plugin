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

/**
 * Dynamically calls static classes based on the current request
 *
 * @return mixed
 * @author Israel D. Canasa
 */
function call()
{
	// Get all the arguments passed
	$args = func_get_args();
	
	// Get the first argument, it's expected to be class::method
	$call = array_shift($args);

	// Pass the class and method to these variables
	list($class, $method) = explode('::', $call);
	
	try
	{
		// Get the prefix oc the current application
		$requestclass = Request::current()->extension_prefix.$class;

		// Call the the method based on the current application
		return call_user_func_array(array($requestclass, $method), $args);
	}
	catch(Exception $e1)
	{
		// Try again, but this time, let's call it without the application prefix. This Will look for the class in the module paths.
		try
		{
			// Call the the method based on the current application
			return call_user_func_array(array($class, $method), $args);
		}
		catch(Exception $e1)
		{
			// Return Null if the call fails. TODO: Throw a better exception
			return NULL;
		}
	}
}
