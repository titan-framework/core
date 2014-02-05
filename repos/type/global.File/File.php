<?
class File extends Integer
{
	protected $mimeTypes = array ();
	
	protected $ownerOnly = FALSE;
	
	protected $showDetails = TRUE;
	
	protected $resolution = 100;
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('owner-only', $field))
			$this->setOwnerOnly (strtoupper (trim ($field ['owner-only'])) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('show-details', $field))
			$this->showDetails = strtoupper (trim ($field ['show-details'])) == 'TRUE' ? TRUE : FALSE;
		
		if (array_key_exists ('resolution', $field) && is_numeric ($field ['resolution']))
			$this->resolution = (int) trim ($field ['resolution']);
		
		if (array_key_exists ('mime-type', $field))
		{
			$archive = Archive::singleton ();
			
			if (!is_array ($field ['mime-type']))
				$field ['mime-type'] = array ($field ['mime-type']);
			
			foreach ($field ['mime-type'] as $trash => $item)
				if ($archive->isAcceptable ($item))
					$this->mimeTypes [] = $item;
		}
	}
	
	public function setValue ($value)
	{
		if (is_null ($value) || (is_numeric ($value) && (int) $value === 0) || (is_string ($value) && $value === ''))
			$this->value = NULL;
		else
			$this->value = $value;
	}
	
	public function setOwnerOnly ($ownerOnly)
	{
		$this->ownerOnly = (bool) $ownerOnly;
	}
	
	public function ownerOnly ()
	{
		return $this->ownerOnly;
	}
	
	public function getFilter ()
	{
		return implode (',', $this->mimeTypes);
	}
	
	public function isAcceptable ($mime)
	{
		if (sizeof ($this->mimeTypes))
			return in_array ($mime, $this->mimeTypes);
		
		return Archive::singleton ()->isAcceptable ($mime);
	}
	
	public function getInfo ()
	{
		if (!$this->getValue ())
			return NULL;
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("SELECT _name, _size, _mimetype, _description FROM _file WHERE _id = ". $this->getValue ());
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (!$obj)
			return NULL;
		
		return array ('_NAME_' => $obj->_name,
					  '_SIZE_' => $obj->_size,
					  '_MIME_' => $obj->_mimetype,
					  '_DESC_' => $obj->_description);
	}
	
	public function isEmpty ()
	{
		return is_null ($this->getValue ()) || !is_numeric ($this->getValue ()) || $this->getValue () == 0;
	}
	
	public function showDetails ()
	{
		return $this->showDetails;
	}
	
	public function getResolution ()
	{
		return $this->resolution;
	}
}
?>