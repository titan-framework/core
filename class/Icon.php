<?php
class Icon
{
	private $icon = NULL;
	
	const ACTION  = 'IconAction';
	const JS 	  = 'IconJs';
	const AJAX    = 'IconAjax';
	const STATUS  = 'IconStatus';
	const COPY	  = 'IconCopy';
	
	public function __construct ($icon, $view = NULL)
	{
		$this->icon = self::factory ($icon, $view);
	}
	
	static public function factory ($icon, $view = NULL)
	{
		if (!is_array ($icon))
			return NULL;
		
		if (array_key_exists ('function', $icon) && trim ($icon ['function']) != '')
			if ($icon ['function'] == '[ajax]')
				$drive = 'InPlace';
			elseif ($icon ['function'] == '[status]')
				$drive = 'Status';
			elseif ($icon ['function'] == '[copy]')
				$drive = 'Copy';
			else
				$drive = 'Js';
		elseif (array_key_exists ('action', $icon))
			$drive = 'Action';
		else
			return NULL;
		
		if (!file_exists (Instance::singleton ()->getReposPath () .'icon/'. $drive .'/'. $drive .'.php'))
			return NULL;
		
		require_once Instance::singleton ()->getReposPath () .'icon/'. $drive .'/'. $drive .'.php';
		
		$class = 'Icon'. $drive;
		
		if (!class_exists ($class, FALSE))
			return NULL;
		
		return new $class ($icon, $view);
	}
	
	public function __call ($name, $args)
	{
		if (!is_object ($this->icon))
			return NULL;
		
		return call_user_func_array (array (&$this->icon, $name), $args);
	}
}
?>