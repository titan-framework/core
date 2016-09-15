<?php
abstract class MenuItem
{
	protected $label = '';
	
	protected $image = '';
	
	abstract public function __construct ($input);
	
	abstract public function getMenuItem ();
	
	abstract public function getSubmenuItem ();
	
	protected static function imageUrl ($file)
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);

		if (file_exists ($section->getCompPath () .'_menu/'. $file))
			return $section->getCompPath () .'_menu/'. $file;

		return Skin::singleton ()->getIconsMenu () . $file;
	}
	
	public function getImage ()
	{
		return $this->image;
	}
	
	public function getImagePath ()
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);
		
		if (file_exists ($section->getCompPath () .'_menu/'. $this->image))
			return $section->getCompPath () .'_menu/'. $this->image;
		
		return Instance::singleton ()->getCorePath () .'interface/menu/'. $this->image;
	}
	
	public function getLabel ()
	{
		return $this->label;
	}
	
	public function getDoc ()
	{
		$path = Instance::singleton ()->getReposPath () .'menu/'. substr (get_class ($this), 4) .'/_doc/'. Localization::singleton ()->getLanguage () .'.txt';
		
		if (file_exists ($path))
			return file_get_contents ($path);
		
		return '';
	}
}
?>