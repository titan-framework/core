<?php
class IconStatus extends IconItem
{
	private $status = array ();
	
	private $column = '';
	
	private $message = '';
	
	private $table = '';
	
	private $primary = '';
	
	private $buffer = NULL;
	
	public function __construct ($icon, $view = NULL)
	{
		parent::__construct ($icon, $view);
		
		if (array_key_exists ('status', $icon) && is_array ($icon ['status']))
			$this->status = $icon ['status'];
		
		if (array_key_exists ('table', $icon) && trim ($icon ['table']) != '')
			$this->table = trim ($icon ['table']);
		elseif (!is_null ($this->view))
			$this->table = $this->view->getTable ();
		
		if (array_key_exists ('primary', $icon) && trim ($icon ['primary']) != '')
			$this->primary = trim ($icon ['primary']);
		elseif (!is_null ($this->view))
			$this->primary = $this->view->getPrimary ();
		
		if (array_key_exists ('column', $icon))
			$this->column = trim ($icon ['column']);
		
		if (array_key_exists ('message', $icon))
			$this->message = translate ($icon ['message']);
		
		foreach ($this->status as $key => $status)
			if (array_key_exists ('value', $status) && trim ($status ['value']) != '')
				User::singleton ()->register ($this->table, $this->column, $status ['value']);
	}
	
	public function makeIcon ($itemId, $forceDisable = FALSE)
	{
		$opts = $this->genOptions ();
		
		if (!$this->accessible || $forceDisable || $this->table == '' || $this->primary == '' || $this->column == '' || trim ($opts) == '')
			return '<img src="'. $this->iconImage ('grey/'. $this->image) .'" border="0" />';
		
		foreach ($this->status as $key => $status)
			if (array_key_exists ('value', $status) && trim ($status ['value']) != '')
				User::singleton ()->register ($this->table, $this->column, $itemId, $status ['value']);
		
		return '<img src="'.  $this->iconImage ($this->image) .'" class="icon" border="0" title="'. $this->label .'" alt="'. $this->label .'"  onclick="JavaScript: inPlaceStatus (\''. (is_null ($this->getId ()) ? '_STATUS_' : $this->getId ()) .'\', \''. $itemId .'\', \''. $this->table .'\', \''. $this->primary .'\', \''. $this->column .'\', \''. htmlentities ($this->message, ENT_QUOTES, 'UTF-8') .'\', this, '. $opts .');" />';
	}
	
	public function makeLink ($itemId, $forceDisable = FALSE)
	{
		$opts = $this->genOptions ();
		
		if (!$this->accessible || $forceDisable || $this->table == '' || $this->primary == '' || $this->column == '' || trim ($opts) == '')
			return 'href="#"';
		
		foreach ($this->status as $key => $status)
			if (array_key_exists ('value', $status) && trim ($status ['value']) != '')
				User::singleton ()->register ($this->table, $this->column, $itemId, $status ['value']);
		
		return 'href="#" onclick="JavaScript: inPlaceStatus (\''. (is_null ($this->getId ()) ? '_STATUS_' : $this->getId ()) .'\', \''. $itemId .'\', \''. $this->table .'\', \''. $this->primary .'\', \''. $this->column .'\', \''. htmlentities ($this->message, ENT_QUOTES, 'UTF-8') .'\', this, '. $opts .');"';
	}
	
	private function genOptions ()
	{
		if (!is_null ($this->buffer))
			return $this->buffer;
		
		$array = array ();
		
		foreach ($this->status as $trash => $opt)
		{
			if (!array_key_exists ('value', $opt) || trim ($opt ['value']) == '' || !array_key_exists ('label', $opt) || trim ($opt ['label']) == '')
				continue;
			
			$array [] = "value: '". trim ($opt ['value']) ."', label: '". htmlentities (translate ($opt ['label']), ENT_QUOTES, 'UTF-8') ."', color: '". (array_key_exists ('color', $opt) ? trim ($opt ['color']) : '') ."'";
		}
		
		if (!sizeof ($array))
			$this->buffer = '';
		else
			$this->buffer = 'new Array ({'. implode ('}, {', $array) .'})';
		
		return $this->buffer; 
	}
}
?>