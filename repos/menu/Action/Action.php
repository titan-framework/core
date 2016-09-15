<?php
class MenuAction extends MenuItem
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
			$this->label = $this->action->getLabel ();
		
		if (isset ($input ['image']) && trim ($input ['image']) != '')
			$this->image = $input ['image'];
		else
			$this->image = $this->action->getName () .'.png';
	}
	
	public function getMenuItem ()
	{
		$user = User::singleton ();
		
		if ($user->accessSection ($this->section->getName ()) && $user->accessAction ($this->action->getName (), $this->section->getName ()))
			return '<li class="cItemLong" onclick="JavaScript: document.location = \'titan.php?target=body&amp;toSection='. $this->section->getName () .'&amp;toAction='. $this->action->getName () .'&itemId='. $this->itemId .'\';"><img align="left" src="'. self::imageUrl ($this->image) .'" title="'. $this->label .'" alt="'. $this->label .'" />'. $this->label .'</li>';

		return '<li class="cItemLongDisabled" onclick="JavaScript: message (\''. __ ('You do not have permission to access the action [1]!', $this->label) .'\', 400, 120, true, \''. __ ('Permission Denied') .'\', \'ERROR\');"><img align="left" src="'. self::imageUrl ('grey/'. $this->image) .'" title="'. $this->label .'" alt="'. $this->label .'" />'. $this->label .'</li>';
	}
	
	public function getSubmenuItem ()
	{
		$user = User::singleton ();
		
		if ($user->accessSection ($this->section->getName ()) && $user->accessAction ($this->action->getName (), $this->section->getName ()))
			return '<li class="cSubItem" onclick="JavaScript: document.location = \'titan.php?target=body&amp;toSection='. $this->section->getName () .'&amp;toAction='. $this->action->getName () .'&itemId='. $this->itemId .'\';"><div>'. $this->label .'</div></li>';

		return '<li class="cSubItemDisabled" style="color: #CCC;" onclick="JavaScript: message (\''. __ ('You do not have permission to access the action [1]!', $this->label) .'\', 400, 120, true, \''. __ ('Permission Denied') .'\', \'ERROR\');"><div>'. $this->label .'</div></li>';
	}
	
	public function getDoc ()
	{
		return $this->section->getDoc ($this->action->getName ());
	}
}
?>