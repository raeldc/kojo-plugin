<?php defined('SYSPATH') or die('No direct script access.');

class Field_JSON extends Jelly_Field
{
	
	public function get($model, $value)
	{
		return new Registry_JSON($value);
	}
	
	public function save($model, $value, $loaded)
	{	
		$json = new Registry_JSON($value);
		return $json->render();
	}
}