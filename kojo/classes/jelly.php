<?php defined('SYSPATH') or die('No direct script access.');

abstract class Jelly extends Jelly_Core
{
	/**
	 * Returns the prefix to use for all models and builders.
	 *
	 * @return  string
	 */
	public static function model_prefix()
	{
		// Gets the current request's extension prefix
		return Request::current()->extension_prefix.Jelly::$_model_prefix;
	}
}