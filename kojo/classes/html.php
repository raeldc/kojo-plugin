<?php defined('SYSPATH') OR die('No direct access allowed.');

class HTML extends Kohana_HTML {
	
	/**
	 * Create HTML link anchors. Note that the title is not escaped, to allow
	 * HTML elements within links (images, etc).
	 *
	 *     echo self::anchor('/user/profile', 'My Profile');
	 *
	 * @modified_by	Israel D. Canasa
	 *
	 * @param   string  URL or URI string
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @param   string  use a specific protocol
	 * @return  string
	 * @uses    URL::base
	 * @uses    URL::site
	 * @uses    self::attributes
	 */
	public static function anchor($uri, $title = NULL, array $attributes = NULL, $protocol = NULL)
	{
		if ($title === NULL)
		{
			// Use the URI as the title
			$title = $uri;
		}
		
		$uri = self::uri($uri);
		
		if ($uri === '')
		{
			// Only use the base URL
			$uri = 'index.php?option='.JRequest::getVar('option', '');
		}
		else
		{
			if (strpos($uri, '://') !== FALSE)
			{
				if (self::$windowed_urls === TRUE AND empty($attributes['target']))
				{
					// Make the link open in a new window
					$attributes['target'] = '_blank';
				}
			}
		}

		// Add the sanitized link to the attributes
		$attributes['href'] = JRoute::_($uri, FALSE);

		return '<a'.self::attributes($attributes).'>'.$title.'</a>';
	}
	
	public static function uri($uri)
	{
		$uri = 'index.php?option='.JRequest::getVar('option', '').'&'.self::array_to_urlvars($uri);
		return JRoute::_($uri, FALSE);
	}
	
	public function array_to_urlvars($uri)
	{
		if (is_array($uri)) 
		{
			$segments = array();
			foreach ($uri as $key => $value) 
			{
				$segments[] = $key.'='.$value;
			}
			$uri = implode('&', $segments);
		}
		
		return $uri;
	}
	
	public function ordering($current, $table, $title, $route = 'default', $params = NULL)
	{
		$session = JFactory::getSession();
		
		$ordering = ($current == 'asc') ? 'desc': 'asc';
		$params = (is_array($params)) ? $params : array();
		
		$params = array_merge($params, array(
			'ordering' => $ordering,
			'table' => $table,
		));
		
		$url = self::array_to_urlvars($params);

		$current_table = $session->get($url.'-current-table');
		
		$image = self::image(JURI::root().'media/system/images/sort_'.$current.'.png');
		
		$session->set($url.'-current-table', $table);
		
		return self::anchor($url, JText::_($title).$image);
	}
}
