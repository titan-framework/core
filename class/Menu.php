<?php
class Menu
{
	static private $menu = FALSE;

	private $array = array ();

	const ACTION  = 'MenuAction';
	const PRINTER = 'MenuPrint';
	const PDF     = 'MenuPdf';
	const CSV	  = 'MenuCsv';
	const SEARCH  = 'MenuSearch';
	const JS      = 'MenuJs';
	const RSS     = 'MenuRss';
	const SAVE    = 'MenuSave';
	const DELETE  = 'MenuDelete';
	const SUBMENU = 'MenuSubmenu';

	public final function __construct ($input)
	{
		if (is_array ($input))
			foreach ($input as $trash => $item)
			{
				if (array_key_exists ('function', $item) && trim ($item ['function']) != '')
					$drive = ucfirst ($item ['function']);
				elseif (array_key_exists ('action', $item))
					$drive = 'Action';
				else
					continue;
				
				if ($aux = self::factory ($drive, $item))
					if (array_key_exists ('id', $item) && trim ($item ['id']) != '')
						$this->array [$item ['id']] = $aux;
					else
						$this->array [] = $aux;
			}
	}
	
	static public function factory ($drive, $array)
	{
		if (!file_exists (Instance::singleton ()->getReposPath () .'menu/'. $drive .'/'. $drive .'.php'))
			return NULL;
		
		require_once Instance::singleton ()->getReposPath () .'menu/'. $drive .'/'. $drive .'.php';
		
		$class = 'Menu'. $drive;
		
		if (!class_exists ($class, FALSE))
			return NULL;
		
		return new $class ($array);
	}
	
	static public function singleton ($input = FALSE)
	{
		if (self::$menu !== FALSE)
			return self::$menu;

		$class = __CLASS__;

		self::$menu = new $class ($input);

		return self::$menu;
	}
	
	public function remove ($match)
	{
		foreach ($this->array as $key => $item)
			if (get_class ($item) == $match)
				unset ($this->array [$key]);
	}

	public function isEmpty ()
	{
		return !sizeof ($this->array);
	}
	
	public function add ($action, $label, $itemId, $section, $img = FALSE, $id = NULL)
	{
		if (is_null ($id) || empty ($id))
			$this->array [] = self::factory ('Action', array ('action' => $action, 'label' => $label, 'itemId' => $itemId, 'section' => $section, 'image' => $img));
		else
			$this->array [$id] = self::factory ('Action', array ('action' => $action, 'label' => $label, 'itemId' => $itemId, 'section' => $section, 'image' => $img));
	}
	
	public function del ($id)
	{
		if (array_key_exists ($id, $this->array))
			unset ($this->array [$id]);
	}

	public function addPrint ($label = '', $img = '')
	{
		$this->array [] = self::factory ('Print', array ('label' => $label, 'image' => $img));
	}

	public function addPdf ($label = '', $img = '')
	{
		$this->array [] = self::factory ('Pdf', array ('label' => $label, 'image' => $img));
	}
	
	public function addCsv ($label = '', $img = '')
	{
		$this->array [] = self::factory ('Csv', array ('label' => $label, 'image' => $img));
	}

	public function addSearch ($label = '', $img = '')
	{
		$this->array [] = self::factory ('Search', array ('label' => $label, 'image' => $img));
	}

	public function addJavaScript ($label, $img, $js)
	{
		$this->array [] = self::factory ('Js', array ('label' => $label, 'image' => $img, 'js' => $js));
	}

	public function addRss ($label = '', $img = '')
	{
		$this->array [] = self::factory ('Rss', array ('label' => $label, 'image' => $img));
	}

	public function addSave ($label = '', $img = '', $goTo = '')
	{
		$this->array [] = self::factory ('Save', array ('label' => $label, 'image' => $img, 'go-to' => $goTo));
	}

	public function addDelete ($label = '', $img = '')
	{
		$this->array [] = self::factory ('Delete', array ('label' => $label, 'image' => $img));
	}

	public function addSubmenu ($label, $img, $item)
	{
		$this->array [] = self::factory ('Submenu', array ('label' => $label, 'image' => $img, 'item' => $item));
	}
	
	public function get ()
	{
		$item = each ($this->array);

		if ($item === FALSE)
		{
			reset ($this->array);

			return NULL;
		}

		return $item ['value']->getMenuItem ();
	}
	
	public function getItem ()
	{
		$item = each ($this->array);

		if ($item === FALSE)
		{
			reset ($this->array);

			return NULL;
		}

		return $item ['value'];
	}
}
?>