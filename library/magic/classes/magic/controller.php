<?php defined('SYSPATH') or die('404 Not Found.');

class Magic_Controller extends Controller
{
	protected $_action;
	
	public function before()
	{
		parent::before();

		// Save the old action so it can be brought back on after
		$this->_action = $this->request->action;
		
		// Set the current action
		$current_action = $this->request->action;
		
		$id = $this->request->param('id', NULL);
		
		// Let's guess the action based on the params
		if ( ! in_array($this->request->action, array('edit', 'add', 'delete')) AND (! is_null($id) OR ! empty($id))) 
		{
			$current_action = 'read';
		}

		if ( ! method_exists($this, 'action_'.$this->request->action)) 
		{
			$model = Jelly::select(Inflector::singular($this->request->controller));
			
			foreach ($model->get_state() as $key => $value) 
			{
				$param = $this->request->param($key, NULL);
				if ( ! is_null($param)) 
				{
					$model->set_state($key, $param);
				}
			}

			$this->request->response = Kostache::factory($this->request->controller.'/'.$current_action)
				->set_model($model)
				->render();

			// Since the magic has been executed, just execute an empty action
			$this->request->action = 'default';
		}
	}
	
	public function action_default()
	{
		// Return the original action
		$this->request->action = $this->_action;
	}
	
	public function after()
	{
		return parent::after();
	}
}