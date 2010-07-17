<?php defined('SYSPATH') or die('No direct script access.');

class Magic_Jelly_Builder extends Jelly_Builder_Core
{
	
	protected $_state;
	
	public function __construct($model = NULL, $type = NULL)
	{
		// Set the default states
		$this->_state = array(
			'offset' => NULL,
			'limit' => NULL,
			'order_column' => ':primary_key',
			'ordering' => 'asc',
			'total' => 0,
			'current_page' => 1
		);
		
		parent::__construct($model, $type);
	}
	
	public function set_state($value, $default = NULL)
	{
		if (is_array($value)) 
		{
			$this->_state = array_merge($this->_state, $value);
			return $this;
		}
		
		if (is_string($value)) 
		{
			$this->_state[$value] = $default;
		}
		
		return $this;
	}
	
	public function get_state($key = NULL, $default = NULL)
	{
		if (is_null($key)) 
		{
			return $this->_state;
		}
		
		return (isset($this->_state[$key])) ? $this->_state[$key] : $default;
	}
	
	public function count()
	{
		return $this->_state['total'] = parent::count();
	}
	
	/**
	 * Prepare the query based on the current state
	 *
	 * @return void
	 * @author Israel D. Canasa
	 */
	public function apply_state()
	{
		// Calculate the offset based on the limit, total, and current page
		$this->_state['offset'] = (int) (($this->_state['current_page'] - 1) * $this->_state['limit']);
		
		$this->order_by($this->_state['order_column'], $this->_state['ordering']);

		return $this;
	}
	
	/**
	 * Builds the builder into a native query
	 *
	 * @param   string  $type
	 * @return  void
	 */
	public function execute($db = 'default')
	{
		// Only apply the offset and limit on execute
		if ( ! is_null($this->_state['limit'])) 
		{
			$this->limit($this->_state['limit'])
				->offset($this->_state['offset']);
		}

		// Don't repeat queries
		if ( ! $this->_result)
		{
			if ($this->_meta)
			{
				// See if we can use a better $db group
				$db = $this->_meta->db();

				// Select all of the columns for the model if we haven't already
				if ($this->_type === Database::SELECT AND empty($this->_select))
				{
					$this->select('*');
				}
			}
			
			// We've now left the Jelly
			$this->_result = $this->_build()->execute($db);

			// Hand it over to Jelly_Collection if it's a select
			if ($this->_type === Database::SELECT)
			{
				$model = NULL;
				
				// Put the context builder in the meta 
				if ($this->_meta) 
				{
					$model = $this->_meta->model();
					$this->_meta->context('builder', $this);
				}

				$this->_result = new Jelly_Collection($model, $this->_result);

				// If the record was limited to 1, we only return that model
				// Otherwise we return the whole result set.
				if ($this->_limit === 1)
				{
					$this->_result = $this->_result->current();
				}
			}
		}
		// Hand off the result to the Jelly_Collection
		return $this->_result;
	}
}
