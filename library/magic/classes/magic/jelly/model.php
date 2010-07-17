<?php defined('SYSPATH') or die('No direct script access.');

class Magic_Jelly_Model extends Jelly_Model_Core
{
	/**
	 * Added for easy browsing of items using next and previous
	 *		I don't know how to explain how it works.
	 *
	 * @param string $direction 
	 * @return Jelly_Model
	 * @author Israel D. Canasa
	 */
	public function _getnext($direction = 'next')
	{
		// Get the builder. The context() method is an extended function of Jelly_Meta.
		//		This was added to get the context of the current object
		$builder = $this->meta()->context('builder')->reset();
		
		// Set the default comparison operators for next
		$next = '>';
		$previous = '<';
		
		// Get the original states so we can restore it later
		$state = $builder->get_state();
		
		// Invert the operations when we want to get the previous item
		if ($direction == 'previous') 
		{
			// Invert the ordering to make sure it gets the next one immediate to the current item
			($state['ordering'] == 'asc') 
				? $builder->set_state('ordering', 'desc')
				: $builder->set_state('ordering', 'asc');
				
			// Invert the comparison operators
			$next = '<';
			$previous = '>';
		}
		
		// Get the next item based on the ordering of table
		// 		We make sure the we know the direction of the list. 
		//		Asc will need ">" to get the next one.
		//		Desc will need "<" to get the next one.
		//	If we're looking for the previous one, we just invert all operations 
		//		which we have already done before this line.
		if ($state['ordering'] == 'asc')
		{
			$builder->where('ordering', $next, $this->ordering);
		}
		else
		{
			// We make sure the we know the direction of the list. 
			$builder->where('ordering', $previous, $this->ordering);
		}
		
		// Apply the builder state and get the object
		$next = $builder
			->set_state('limit', 1)
			->set_state('id', NULL)
			->apply_state()
			->execute();

		// If there's nothing loaded, maybe the next one is the first one
		if ( ! $next->loaded()) 
		{
			// Get the first one
			$next = $builder->reset()
				->set_state('limit', 1)
				->set_state('id', NULL)
				->apply_state()
				->execute();

			$primary_key = $this->meta()->primary_key();
			
			// The first one should not be the same as the current one
			if ($next->$primary_key == $this->$primary_key) 
			{
				return NULL;
			}
		}
		
		// Restore the original ordering state
		$builder->set_state($state);
		
		if ($next->loaded()) 
		{
			return $next;
		}
		
		return NULL;
	}
	
	public function next()
	{
		return $this->_getnext('next');
	}
	
	public function previous()
	{
		return $this->_getnext('previous');
	}
}
