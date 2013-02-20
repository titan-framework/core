<?
class MenuPrint extends MenuItem
{
	private $action = NULL;
	
	private $section = NULL;
	
	private $itemId = 0;
	
	public function __construct ($input)
	{
		$business = Business::singleton ();
		
		if (isset ($input ['section']) && $business->sectionExists ($input ['section']))
			$this->section = $business->getSection ($input ['section']);
		else
			$this->section = $business->getSection (Section::TCURRENT);
		
		if (isset ($input ['action']) && $this->section->actionExists ($input ['action']))
			$this->action = $this->section->getAction ($input ['action']);
		else
			$this->action = $this->section->getAction (Action::TCURRENT);
		
		if (isset ($input ['itemId']))
			$itemId = $input ['itemId'];
		else
		{
			global $itemId;
			
			if (!is_null ($itemId))
				$this->itemId = $itemId;
		}
		
		if (isset ($input ['label']) && trim ($input ['label']) != '')
			$this->label = translate ($input ['label']);
		else
			$this->label = __ ('Print');
		
		if (isset ($input ['image']) && trim ($input ['image']) != '')
			$this->image = $input ['image'];
		else
			$this->image = 'print.png';
	}
	
	public function getMenuItem ()
	{
		return '<li class="cItemLong" onclick="JavaScript: openPrintPopup (\'titan.php?target=print&amp;toSection='. $this->section->getName () .'&amp;toAction='. $this->action->getName () .'&itemId='. $this->itemId .'\');"><img align="left" src="'. self::imageUrl ($this->image) .'" title="'. $this->label .'" alt="'. $this->label .'" />'. $this->label .'</li>';
	}
	
	public function getSubmenuItem ()
	{
		return '<li class="cSubItem" onclick="JavaScript: openPrintPopup (\'titan.php?target=print&amp;toSection='. $this->section->getName () .'&amp;toAction='. $this->action->getName () .'&itemId='. $this->itemId .'\');"><div>'. $this->label .'</div></li>';
	}
}
?>