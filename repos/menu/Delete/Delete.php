<?php
class MenuDelete extends MenuItem
{
	private $itemId = 0;
	
	public function __construct ($input)
	{
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
			$this->label = __ ('Delete');
		
		if (isset ($input ['image']) && trim ($input ['image']) != '')
			$this->image = $input ['image'];
		else
			$this->image = 'delete.png';
	}
	
	public function getMenuItem ()
	{
		global $form;
		
		if (!is_object ($form))
			return '';
		
		return '<li class="cItemLong" onclick="JavaScript: deleteForm (\''. $form->getFile () .'\', \'form_'. $form->getAssign () .'\', \''. $this->itemId .'\');"><img align="left" src="'. self::imageUrl ($this->image) .'" title="'. $this->label .'" alt="'. $this->label .'" />'. $this->label .'</li>';
	}
	
	public function getSubmenuItem ()
	{
		global $form;
		
		if (!is_object ($form))
			return '';
		
		return '<li class="cSubItem" onclick="JavaScript: deleteForm (\''. $form->getFile () .'\', \'form_'. $form->getAssign () .'\', \''. $this->itemId .'\');"><div>'. $this->label .'</div></li>';
	}
}
?>