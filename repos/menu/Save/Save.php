<?php
class MenuSave extends MenuItem
{
	private $goTo = FALSE;
	
	private $itemId = 0;
	
	public function __construct ($input)
	{
		if (isset ($input ['label']) && trim ($input ['label']) != '')
			$this->label = translate ($input ['label']);
		else
			$this->label = __ ('Save');
		
		if (isset ($input ['image']) && trim ($input ['image']) != '')
			$this->image = $input ['image'];
		else
			$this->image = 'save.png';
		
		if (isset ($input ['itemId']))
			$itemId = $input ['itemId'];
		else
		{
			global $itemId;
			
			if (!is_null ($itemId))
				$this->itemId = $itemId;
		}
		
		if (isset ($input ['go-to']) && trim ($input ['go-to']) != '')
			$this->goTo = $input ['go-to'];
	}
	
	public function getMenuItem ()
	{
		global $form;
		
		return '<li class="cItemLong" onclick="JavaScript: saveForm (\''. $form->getFile () .'\', \'form_'. $form->getAssign () .'\', \''. $this->itemId .'\''. ($this->goTo !== FALSE ? ', \''. $this->goTo .'\'' : '') .');"><img align="left" src="'. self::imageUrl ($this->image) .'" title="'. $this->label .'" alt="'. $this->label .'" />'. $this->label .'</li>';
	}
	
	public function getSubmenuItem ()
	{
		global $form;
		
		return '<li class="cSubItem" onclick="JavaScript: saveForm (\''. $form->getFile () .'\', \'form_'. $form->getAssign () .'\', \''. $this->itemId .'\''. ($this->goTo !== FALSE ? ', \''. $this->goTo .'\'' : '') .');"><div>'. $this->label .'</div></li>';
	}
}
?>