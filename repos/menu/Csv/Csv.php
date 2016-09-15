<?php
class MenuCsv extends MenuItem
{
	private $section = NULL;
	
	public function __construct ($input)
	{
		$business = Business::singleton ();
		
		if (isset ($input ['section']) && $business->sectionExists ($input ['section']))
			$this->section = $business->getSection ($input ['section']);
		else
			$this->section = $business->getSection (Section::TCURRENT);
		
		if (isset ($input ['label']) && trim ($input ['label']) != '')
			$this->label = translate ($input ['label']);
		else
			$this->label = __ ('Export in CSV');
		
		if (isset ($input ['image']) && trim ($input ['image']) != '')
			$this->image = $input ['image'];
		else
			$this->image = 'csv.png';
	}
	
	public function getMenuItem ()
	{
		return '<li class="cItemLong" onclick="JavaScript: openPrintPopup (\'titan.php?target=csv&amp;toSection='. $this->section->getName () .'\');"><img align="left" src="'. self::imageUrl ($this->image) .'" title="'. $this->label .'" alt="'. $this->label .'" />'. $this->label .'</li>';
	}
	
	public function getSubmenuItem ()
	{
		return '<li class="cSubItem" onclick="JavaScript: openPrintPopup (\'titan.php?target=csv&amp;toSection='. $this->section->getName () .'\');"><div>'. $this->label .'</div></li>';
	}
}
?>