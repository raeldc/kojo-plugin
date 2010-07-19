<?php defined('SYSPATH') or die('404 Not Found.');

/**
* 
*/
class Registry_JSON
{
	public static function decode($value)
	{
		return new Registry_JSON($value);
	}
	
	protected $_values;
	
	public function __construct($value)
	{
		// Make sure $this->_values is an object
		if (is_string($value)) 
		{
			$this->_values = json_decode($value);
			return;
		}
		
		if (is_array($value)) 
		{
			$this->_values = Arr::to_object($value);
			return;
		}
		
		$this->_values = $value;
	}
	
	public function render()
	{
		return json_encode($this->_values);
	}
	
	public function __toString()
	{
		return $this->render();
	}

	public function __get($name)
	{
		if (isset($this->_values->$name)) 
		{
			return $this->_values->$name;
		}
		
		return NULL;
	}
	
	public function __set($name, $value = '')
	{
		$this->_values->$name = $value;
	}
	
	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}
	
	public function offsetGet($offset)
	{
		$this->__get($offset);
	}
	
	public function offsetExists($offset)
	{
		return isset($this->_values->$offset);
	}
	
	public function offsetUnset($offset)
	{
		unset($this->_values->$offset);
	}
}
