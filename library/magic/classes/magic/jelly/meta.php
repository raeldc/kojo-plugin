<?php defined('SYSPATH') or die('No direct script access.');

class Magic_Jelly_Meta extends Jelly_Meta_Core
{
	protected $_builder = NULL;
	protected $_collection = NULL;
	
	public function context($type, $value = NULL)
	{
		$type = '_'.(string) $type;

		if ( ! is_null($value)) 
		{
			$this->$type = $value;
		}
		
		return $this->$type;
	}
}
