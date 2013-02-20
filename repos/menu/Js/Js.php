<?
class MenuJs extends MenuItem
{
	private $js = '';
	
	public function __construct ($input)
	{
		if (isset ($input ['js']) && trim ($input ['js']) != '')
			$this->js = $input ['js'];
		else
			$this->js = "message ('". __ ('This functionality is inactive!') ."', 300, 120, true, '". __ ('Functionality Inactive') ."', 'ERROR')";
		
		if (isset ($input ['label']) && trim ($input ['label']) != '')
			$this->label = translate ($input ['label']);
		else
			$this->label = __ ('Undefined');
		
		if (isset ($input ['image']) && trim ($input ['image']) != '')
			$this->image = $input ['image'];
		else
			$this->image = 'edit.png';
	}
	
	public function getMenuItem ()
	{
		return '<li class="cItemLong" onclick="JavaScript: '. $this->js .';"><img align="left" src="'. self::imageUrl ($this->image) .'" title="'. $this->label .'" alt="'. $this->label .'" />'. $this->label .'</li>';
	}
	
	public function getSubmenuItem ()
	{
		return '<li class="cSubItem" onclick="JavaScript: '. $this->js .';"><div>'. $this->label .'</div></li>';
	}
}
?>