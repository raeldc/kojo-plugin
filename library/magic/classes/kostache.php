<?php

class KOstache extends Mustache
{
	public static $globals = array();
	
	final public static function factory($path, $template = null, $view = null, $partials = null)
	{
		static $instances;

		if ( ! is_array($instances)) 
		{
			$instances = array();
		}
		
		if (isset($instances[$path])) 
		{
			if ($instances[$path]->get_template() != $template) 
			{
				$instances[$path]->fetch_template($template);
			}

			return $instances[$path];
		}
		
		if (is_null($template)) 
			$template = $path;
		
		$appclass = Request::current()->extension_prefix.'view_'.str_replace('/', '_', $path);

		if ( ! class_exists($appclass))
			$class = 'View_'.str_replace('/', '_', $path);
		else
			return $instances[$path] = new $appclass($template, $view, $partials);
			
		if (isset($class) AND ! class_exists($class))
			throw new Kohana_View_Exception('Missing Kostache View Class for ":appclass" or ":class"', array(':appclass' => $appclass, ':class' => $class));
		else
			return $instances[$path] = new $class($template, $view, $partials);
	}
	
	final public function set_global($key, $value = NULL)
	{
		if (is_array($key) AND Arr::is_assoc($key)) 
		{
			Kostache::$globals = Arr::merge(Kostache::$globals, $key);
		}
		else
		{
			Kostache::$globals[$key] = $value;
		}
	}
	
	protected $_model;
	protected $_template_path;
	
	final public function __construct($template = null, $view = null, $partials = null)
	{
		parent::__construct($template, $view, $partials);

		$this->_charset = Kohana::$charset;
		$this->base_url = JURI::base(TRUE);
		
		// Convert partials to expanded template strings
		foreach ($this->_partials as $key => $partial_template)
		{
			if ($location = Kohana::find_file('tmpl', $partial_template, 'mustache'))
			{
				$this->_partials[$key] = file_get_contents($location);
			}
		}
		
		$this->initialize();

		$this->fetch_template($template);
	}
	
	public function initialize()
	{
		
	}
	
	public function render($template = null, $view = null, $partials = null)
	{
		foreach (Kostache::$globals as $key => $value) {
			if ( ! isset($this->{$key}) AND ! method_exists($this, $key)) 
			{
				$this->set($key, $value);
			}
		}

		return parent::render($template, $view, $partials);
	}
	
	public function fetch_template($template = NULL)
	{
		// Override the template location to match kohana's conventions
		if (is_null($template))
		{
			$class = str_replace(Request::current()->extension_prefix, '', get_class($this));
			$foo = explode('_', $class);
			array_shift($foo);
			$this->_template_path = implode('/', $foo);
		}
		else
		{
			$this->_template_path = $template;
		}

		$template = Kohana::find_file('tmpl', $this->_template_path, 'mustache');

		if ($template)
			$this->_template = file_get_contents($template);
		else
			throw new Kohana_Exception('Template file not found: :path :template', array(':path' => $this->_template_path, ':template' => $template));

		return $this->_template;
	}
	
	public function get_template()
	{
		return $this->_template_path;
	}
	
	public function set_partials($partials)
	{
		// Convert partials to expanded template strings
		foreach ($partials as $key => $partial_template)
		{
			if ($location = Kohana::find_file('tmpl', $partial_template, 'mustache'))
			{
				$this->_partials[$key] = file_get_contents($location);
			}
		}
	}
	
	/**
	 * Assign the model. Usually called by the controller.
	 * 		Expects either a collection(Iterable) or an object(ArrayAccesss or just Object)
	 *
	 * @param string $model 
	 * @return void
	 * @author Israel D. Canasa
	 */
	public function set_model(&$model)
	{
		$this->_model = $model;
		
		return $this;
	}
	
	public function get_model()
	{
		return $this->_model;
	}
	
	/**
	 * Assigns a variable by name.
	 *
	 *     // This value can be accessed as {{foo}} within the template
	 *     $view->set('foo', 'my value');
	 *
	 * You can also use an array to set several values at once:
	 *
	 *     // Create the values {{food}} and {{beverage}} in the template
	 *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
	 *
	 * @param   string   variable name or an array of variables
	 * @param   mixed    value
	 * @return  $this
	 */
	public function set($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $name => $value)
			{
				$this->{$name} = $value;
			}
		}
		else
		{
			$this->{$key} = $value;
		}

		return $this;
	}

	/**
	 * Assigns a value by reference. The benefit of binding is that values can
	 * be altered without re-setting them. It is also possible to bind variables
	 * before they have values. Assigned values will be available as a
	 * variable within the template file:
	 *
	 *     // This reference can be accessed as {{ref}} within the template
	 *     $view->bind('ref', $bar);
	 *
	 * @param   string   variable name
	 * @param   mixed    referenced variable
	 * @return  $this
	 */
	public function bind($key, & $value)
	{
		$this->{$key} =& $value;

		return $this;
	}
	
	public function pagination()
	{
		$model = $this->get_model();
		
		$pagination = Pagination::factory(array(
			'current_page' => array('source' => 'route', 'key' => 'current_page'),
			'total_items' => $this->total,
			'items_per_page' => $model->get_state('limit'),
		));
		
		Session::instance()->set(Request::current()->controller.'.current_page', $pagination->current_page);

		return $pagination->render();
	}
}