<?php defined('SYSPATH') or die('No direct script access.');

class KoJo extends Kohana_Core 
{
	
	protected static $_otherpaths = array();
	
	/**
	 * Add additional paths on top of the module paths. 
	 * Additional paths will be appended to existing additional paths
	 *
	 * @param mixed $path 
	 * @return void
	 * @author Israel D. Canasa
	 */
	public static function add_path($path)
	{
		$paths = array();

		if (is_string($path) AND is_dir($path)) 
		{
			$paths = array($path);
		}
		
		if (is_array($path)) 
		{
			$paths = $path;
		}
		
		// Always add paths below the current paths
		self::$_otherpaths = array_merge(self::$_otherpaths, $paths);
		self::$_paths = array_merge(self::$_otherpaths, self::$_paths);
	}
	
	/**
	 * Get the path and file of a classname based on the convention
	 *
	 * @param string class name
	 * @return array path and file
	 * @author Israel D. Canasa
	 */
	public static function get_path_from_class($class)
	{
		$class = strtolower($class);
		
		if (array_key_exists($ext = substr($class, 0, 3), $extensions = array('com' => 'components', 'mod' => 'modules', 'plg' => 'plugins'))) 
		{
			// Strip the first 3 letters to get the name of the extension that's being loaded
			$name = substr($class, 3, strpos($class, '_') - 3);

			// If _admin is detected right after the name and if it's not a plugin
			if ($ext != 'plg' AND substr($class, strpos($class, '_') + 1, 5) == 'admin') 
			{
				// Get the path by getting the rest of the class name removing the first 2 parts.
				//		ComName_Admin_Controller_Default will be converted into controller/default
				$file = str_replace('_', DS, str_replace($ext.$name.'_admin_', '', $class));
				$path = JPATH_ADMINISTRATOR.DS.$extensions[$ext].DS.$ext.'_'.$name.DS;
			}
			// If no admin is detected, we use the site path
			else
			{
				// ComName_Controller_Default will be converted into controller/default
				$file = str_replace('_', DS, str_replace($ext.$name.'_', '', $class));
				$path = JPATH_ROOT.DS.$extensions[$ext].DS.$ext.'_'.$name.DS;
			}
			
			return array('path' => $path, 'file' => $file);
		}
		
		return FALSE;
	}
	
	/**
	 * Provides auto-loading support of Kohana classes, as well as transparent
	 * extension of classes that have a _Core suffix.
	 *
	 * Class names are converted to file names by making the class name
	 * lowercase and converting underscores to slashes:
	 *
	 *     // Loads classes/my/class/name.php
	 *     Kohana::auto_load('My_Class_Name');
	 *
	 * KoJo Modification
	 *		Autoload Component Module or Plugin classes using a conventional prefix
	 *			eg: [Com][Mod][Plg]ExtentionName
	 *
	 * @param   string   class name
	 * @return  boolean
	 */
	public static function auto_load($class)
	{
		// TODO: Find a better way to get plugin paths
		if ($path = self::get_path_from_class($class)) 
		{
			if (is_file($path = $path['path'].'classes'.DS.$path['file'].EXT)) 
			{
				require $path;
				return true;
			}
		}
		
		// Transform the class name into a path
		$file = str_replace('_', DS, $class);

		if ($path = Kohana::find_file('classes', $file))
		{
			// Load the class file
			require $path;

			// Class has been found
			return TRUE;
		}

		// Class is not in the filesystem
		return FALSE;
	}
	
	/**
	 * Cleans up the environment:
	 *
	 * - Restore the previous error and exception handlers
	 * - Destroy the self::$log and self::$config objects
	 *
	 * @return  void
	 */
	public static function deinit()
	{
		if (self::$_init)
		{
			// Removed the autoloader
			spl_autoload_unregister(array('KoJo', 'auto_load'));

			if (self::$errors)
			{
				// Go back to the previous error handler
				restore_error_handler();

				// Go back to the previous exception handler
				restore_exception_handler();
			}

			// Destroy objects created by init
			self::$log = self::$config = NULL;

			// Reset internal storage
			self::$_modules = self::$_files = array();
			self::$_paths   = array(APPPATH, SYSPATH);

			// Reset file cache status
			self::$_files_changed = FALSE;

			// Kohana is no longer initialized
			self::$_init = FALSE;
		}
	}
	
}