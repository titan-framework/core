<?php
class MenuSubmenu extends MenuItem
{
	private $submenu = array ();
	
	public function __construct ($input)
	{
		if (isset ($input ['label']) && trim ($input ['label']) != '')
			$this->label = translate ($input ['label']);
		else
			$this->label = __ ('Submenu');
		
		if (isset ($input ['image']) && trim ($input ['image']) != '')
			$this->image = $input ['image'];
		else
			$this->image = 'submenu.png';
		
		if (array_key_exists ('item', $input) && is_array ($input ['item']))
			foreach ($input ['item'] as $trash => $subitem)
			{
				if (array_key_exists ('function', $subitem) && trim ($subitem ['function']) != '')
					$class = 'Menu'. ucfirst ($subitem ['function']);
				elseif (array_key_exists ('action', $subitem))
					$class = 'MenuAction';
				else
					continue;
				
				if (!class_exists ($class, FALSE))
					continue;
				
				$this->submenu [] = new $class ($subitem);
			}
	}
	
	public function getMenuItem ()
	{
		ob_start ();
		?>
		<li class="cItemLong">
			<img align="left" src="<?= self::imageUrl ($this->image) ?>" title="<?= $this->label ?>" />
			<div class="dul">
				<ul>
					<?php
					while ($cell = $this->get ())
						echo $cell;
					?>
				</ul>
			</div>
		</li>
		<?php
		return ob_get_clean ();
	}
	
	public function getSubmenuItem ()
	{
		return '';
	}
	
	private function get ()
	{
		$item = each ($this->submenu);

		if ($item === FALSE)
		{
			reset ($this->submenu);

			return NULL;
		}

		return $item ['value']->getSubmenuItem ();
	}
	
	public function getDoc ()
	{
		$buffer = "";
		foreach ($this->submenu as $trash => $item)
			$buffer .= "* ". $item->getLabel () .": ". $item->getDoc () ."\n";
		
		return $buffer;
	}
}
?>