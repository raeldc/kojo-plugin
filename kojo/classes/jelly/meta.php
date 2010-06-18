<?php defined('SYSPATH') or die('No direct script access.');

class Jelly_Meta extends Jelly_Meta_Core
{
	/**
	 * This is called after initialization to
	 * finalize any changes to the meta object.
	 *
	 * @return  void
	 */
	public function finalize($model)
	{
		$modelname = $model;
		
		$prefix_length = strlen(Jelly::model_prefix());

		// Compare the first parts of the names and chomp if they're the same
		if (strtolower(substr($model, 0, $prefix_length)) === strtolower(Jelly::model_prefix()))
		{
			$model = substr($model, $prefix_length);
		}

		if ($this->initialized)
			return;

		// Ensure certain fields are not overridden
		$this->model = $model;
		$this->columns     =
		$this->defaults    =
		$this->field_cache =
		$this->aliases     = array();

		// Table should be a sensible default
		if (empty($this->table))
		{
			$this->table = inflector::plural($model);
		}

		// See if we have a special builder class to use
		if (empty($this->builder))
		{
			
			$builder = Jelly::model_prefix().'builder_'.$model;

			if (class_exists($builder))
			{
				$this->builder = $builder;
			}
			else
			{
				$this->builder = 'Jelly_Builder';
			}
		}

		// Can we set a sensible foreign key?
		if (empty($this->foreign_key))
		{
			$this->foreign_key = $model.'_id';
		}

		// Initialize all of the fields with their column and the model name
		foreach($this->fields as $column => $field)
		{
			// Allow aliasing fields
			if (is_string($field))
			{
				if (isset($this->fields[$field]))
				{
					$this->aliases[$column] = $field;
				}

				// Aliases shouldn't pollute fields
				unset($this->fields[$column]);

				continue;
			}

			$field->initialize($modelname, $column);

			// Ensure a default primary key is set
			if ($field->primary AND empty($this->primary_key))
			{
				$this->primary_key = $column;
			}

			// Set the defaults so they're actually persistent
			$this->defaults[$column] = $field->default;

			// Set the columns, so that we can access reverse database results properly
			if ( ! array_key_exists($field->column, $this->columns))
			{
				$this->columns[$field->column] = array();
			}

			$this->columns[$field->column][] = $column;
		}

		// Meta object is initialized and no longer writable
		$this->initialized = TRUE;
	}
}
