<?php defined('JPATH_BASE') or die();

/**
 * Kojo System plugin
 */
class plgSystemKojo extends JPlugin
{	
	public function InitializeKoJo()
	{
		if (defined('KOJO_INITIALIZED')) 
		{
			return;
		}
		
		$application = JPATH_PLUGINS.DS.'system'.DS.'kojo'.DS.'kojo';
		$modules = JPATH_PLUGINS.DS.'system'.DS.'kojo'.DS.'library';
		$system = JPATH_PLUGINS.DS.'system'.DS.'kojo'.DS.'kohana';

		/**
		 * The default extension of resource files. If you change this, all resources
		 * must be renamed to use the new extension.
		 *
		 * @see  http://kohanaframework.org/guide/about.install#ext
		 */
		define('EXT', '.php');

		// Set the full path to the docroot
		define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

		// Make the application relative to the docroot
		if ( ! is_dir($application) AND is_dir(DOCROOT.$application))
			$application = DOCROOT.$application;

		// Make the modules relative to the docroot
		if ( ! is_dir($modules) AND is_dir(DOCROOT.$modules))
			$modules = DOCROOT.$modules;

		// Make the system relative to the docroot
		if ( ! is_dir($system) AND is_dir(DOCROOT.$system))
			$system = DOCROOT.$system;

		// Define the absolute paths for configured directories
		define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
		define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
		define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);

		// Clean up the configuration vars
		unset($application, $modules, $system);

		// Load the base, low-level functions
		require APPPATH.'base'.EXT;

		// Load the core Kohana class
		require SYSPATH.'classes/kohana/core'.EXT;
		require APPPATH.'classes/kojo'.EXT;
		require SYSPATH.'classes/kohana'.EXT;
		
		/**
		 * Enable the Kohana auto-loader.
		 *
		 * @see  http://kohanaframework.org/guide/using.autoloading
		 * @see  http://php.net/spl_autoload_register
		 */
		spl_autoload_register(array('KoJo', 'auto_load'));

		/**
		 * Enable the Kohana auto-loader for unserialization.
		 *
		 * @see  http://php.net/spl_autoload_call
		 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
		 */
		ini_set('unserialize_callback_func', 'spl_autoload_call');

		KoJo::init(array(
			'base_url' => JURI::base(), 
			'index_file' => 'index.php',
			'cache_dir' => JPATH_SITE.DS.'cache', 
		));
		
		KoJo::$log->attach(
			new Kohana_Log_File(
				JFactory::getConfig()->getValue('log_path')
			)
		);
		
		KoJo::$config->attach(new Kohana_Config_File);
		
		KoJo::modules(array(
			'cache'      => MODPATH.'cache',     // Caching with multiple backends
			'database'   => MODPATH.'database',  	// Database access
			'pagination' => MODPATH.'pagination', 	// Paging of results
			'jelly' => MODPATH.'jelly', 			// The best ORM out there
		));
		
		define('KOJO_INITIALIZED', TRUE);
	}
	
	public function ExitKojo()
	{
		if (defined('KOJO_INITIALIZED')) 
		{
			KoJo::deinit();
		}
	}
}