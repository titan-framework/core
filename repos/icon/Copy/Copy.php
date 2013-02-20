<?
class IconCopy extends IconItem
{
	protected $image = 'copy.gif';
	
	public function __construct ($icon, $view = NULL)
	{
		parent::__construct ($icon, $view);
		
		if (trim ($this->label) == '')
			$this->label = $this->action->getLabel ();
	}
	
	public function makeIcon ($itemId, $forceDisable = FALSE)
	{
		if (!$this->accessible || $forceDisable)
			return '<img src="'. $this->iconImage ('grey/'. $this->image) .'" border="0" />';
		
		return '<img src="'.  $this->iconImage ($this->image) .'" class="icon" border="0" title="'. $this->label .'" alt="'. $this->label .'"  onclick="JavaScript: copyItem (\''. $itemId .'\', \''. $this->action->getName () .'\', \''. $this->section->getName () .'\', this);" />';
	}
	
	public function makeLink ($itemId, $forceDisable = FALSE)
	{
		if (!$this->accessible || $forceDisable)
			return 'href="#"';
		
		return 'href="#" onclick="JavaScript: copyItem (\''. $itemId .'\', \''. $this->action->getName () .'\', \''. $this->section->getName () .'\', this);"';
	}
}
?>