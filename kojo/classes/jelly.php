<?php defined('SYSPATH') or die('No direct script access.');

abstract class Jelly extends Jelly_Core
{
	public static $client;
	
	/**
	 * Returns the model name of a class
	 *
	 * @param   string|Jelly_Model  $model
	 * @return  string
	 */
	public static function model_name($model)
	{
		return Jelly::class_name($model);
	}
	
	/**
	 * Returns the class name of a model
	 *
	 * @param   string|Jelly_Model  $model
	 * @return  string
	 */
	public static function class_name($model)
	{
		if ($model instanceof Jelly_Model)
		{
			return strtolower(get_class($model));
		}
		else
		{
			// Check which client is being used. Client defaults to site
			Jelly::client();
			
			// Get the prefix based on the current Request 
			Jelly::$_model_prefix = (Jelly::$client) ? Request::current()->extension().'_'.'admin_model_' : Request::current()->extension().'_model_';

			return strtolower(Jelly::$_model_prefix.$model);
		}
	}
	
	/**
	 * Returns the prefix to use for all models and builders.
	 *
	 * @return  string
	 */
	public static function model_prefix()
	{
		return Jelly::$_model_prefix;
	}
	
	/**
	 * Automatically loads a model, if it exists,
	 * into the meta table.
	 *
	 * Models are not required to register
	 * themselves; it happens automatically.
	 *
	 * @param   string  $model
	 * @return  boolean
	 */
	public static function register($model)
	{
		$class = $model;

		// Don't re-initialize!
		if (isset(Jelly::$_models[$class]))
		{
			return TRUE;
		}

		 // Can we find the class?
		if (class_exists($class))
		{
			// Prevent accidentally trying to load ORM or Sprig models
			if ( ! is_subclass_of($class, "Jelly_Model"))
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
		
		// Load it into the registry
		Jelly::$_models[$model] = $meta = new Jelly_Meta($model);
		
		// Let the intialize() method override defaults.
		call_user_func(array($class, 'initialize'), $meta);

		// Finalize the changes
		$meta->finalize($model);

		return TRUE;
	}
	
	public static function client($client = NULL)
	{
		if (is_null(self::$client)) 
		{
			self::$client = (JFactory::getApplication()->isAdmin()) ? 'admin' : NULL;
		}else
		{
			self::$client = ($client == 'admin') ? 'admin' : NULL;
		}
	}
}