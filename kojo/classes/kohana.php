<?php defined('SYSPATH') or die('No direct script access.');

class Kohana extends Kohana_Core 
{
	/**
	 * Changes the currently enabled modules. Module paths may be relative
	 * or absolute, but must point to a directory:
	 *
	 *     Kohana::modules(array('modules/foo', MODPATH.'bar'));
	 *
	 * @param   array  list of module paths
	 * @return  array  enabled modules
	 */
	public static function modules(array $modules = NULL)
	{
		if ($modules === NULL)
			return Kohana::$_modules;

		if (Kohana::$profiling === TRUE)
		{
			// Start a new benchmark
			$benchmark = Profiler::start('Kohana', __FUNCTION__);
		}

		foreach ($modules as $name => $path)
		{
			if (is_dir($path))
			{
				// Add the module to include paths
				$paths[] = $modules[$name] = realpath($path).DIRECTORY_SEPARATOR;
			}
			else
			{
				// This module is invalid, remove it
				unset($modules[$name]);
			}
		}

		// Finish the include paths by adding SYSPATH
		$paths[] = SYSPATH;

		// Set the new include paths
		Kohana::$_paths = $paths;

		// Set the current module list
		Kohana::$_modules = $modules;

		foreach (Kohana::$_modules as $path)
		{
			$init = $path.'init'.EXT;

			if (is_file($init))
			{
				// Include the module initialization file once
				require_once $init;
			}
		}

		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}

		return Kohana::$_modules;
	}
	
}