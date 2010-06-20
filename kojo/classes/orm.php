<?php defined('SYSPATH') or die('No direct script access.');

class ORM extends Kohana_ORM
{
	/**
	 * Creates and returns a new model.
	 *
	 * @chainable
	 * @param   string  model name
	 * @param   mixed   parameter for find()
	 * @return  ORM
	 */
	public static function factory($model, $id = NULL)
	{
		// Set class name
		$model = Request::current()->extension_prefix.'model_orm_'.$model;

		return new $model($id);
	}
	
	public function order_by($column = NULL, $direction = 'ASC')
	{
		if (is_array($column)) 
		{
			$this->_sorting = $column;
		}else
		{
			$this->_sorting = array( $column => $direction);
		}
		
		return $this;
	}
}