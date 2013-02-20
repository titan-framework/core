<?
class MenuSearch extends MenuItem
{
	public function __construct ($input)
	{
		if (isset ($input ['label']) && trim ($input ['label']) != '')
			$this->label = translate ($input ['label']);
		else
			$this->label = __ ('Search Itens');
		
		if (isset ($input ['image']) && trim ($input ['image']) != '')
			$this->image = $input ['image'];
		else
			$this->image = 'search.png';
	}
	
	public function getMenuItem ()
	{
		return '<li class="cItemLong" onclick="JavaScript: showSearch ();"><img align="left" src="'. self::imageUrl ($this->image) .'" title="'. $this->label .'" alt="'. $this->label .'" />'. $this->label .'</li>';
	}
	
	public function getSubmenuItem ()
	{
		return '<li class="cSubItem" onclick="JavaScript: showSearch ();"><div>'. $this->label .'</div></li>';
	}
}
?>