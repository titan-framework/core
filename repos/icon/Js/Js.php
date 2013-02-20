<?
class IconJs extends IconItem
{
	private $function = FALSE;
	
	public function __construct ($icon, $view = NULL)
	{
		parent::__construct ($icon, $view);
		
		if (array_key_exists ('function', $icon) && trim ($icon ['function']) != '')
			$this->function = $icon ['function'];
	}
	
	public function makeIcon ($itemId, $forceDisable = FALSE)
	{
		if (!$this->accessible || $forceDisable)
			return '<img src="'. $this->iconImage ('grey/'. $this->image) .'" border="0" />';
		
		return '<img src="'.  $this->iconImage ($this->image) .'" class="icon" border="0" title="'. $this->label .'"  onclick="JavaScript: '. $this->function .' (\''. $itemId .'\', this);" />';
	}
	
	public function makeLink ($itemId, $forceDisable = FALSE)
	{
		if (!$this->accessible || $forceDisable)
			return 'href="#"';
		
		return 'href="#" onclick="JavaScript: '. $this->function .' (\''. $itemId .'\', this);"';
	}
}
?>