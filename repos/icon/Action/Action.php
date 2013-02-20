<?
class IconAction extends IconItem
{
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
		
		return '<a '. $this->makeLink ($itemId) .'><img src="'.  $this->iconImage ($this->image) .'" border="0" title="'. $this->label .'" alt="'. $this->label .'" /></a>';
	}
	
	public function makeLink ($itemId, $forceDisable = FALSE)
	{
		if (!$this->accessible || $forceDisable)
			return 'href="#"';
		
		$vars = '';
		
		$section = Business::singleton ()->getSection (Section::TCURRENT);
		
		if ($this->section->getName () != $section->getName ())
			$vars .= '&amp;fatherId='. $itemId;
		
		foreach ($this->variables as $trash => $var)
		{
			$var = trim ($var);
			
			global $$var;
			
			$vars .= '&amp;'. $var .'='. $$var;
		}
		
		return 'href="titan.php?target=body&amp;toSection='. $this->section->getName () .'&amp;toAction='. $this->action->getName () .'&amp;itemId='. $itemId . $vars .'"';
	}
}
?>