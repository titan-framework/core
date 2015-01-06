<?php

class CloudFile extends File
{	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
	}
	
	public function getInfo ()
	{
		if (!$this->getValue ())
			return NULL;
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("SELECT _name, _size, _mimetype FROM _cloud WHERE _id = :id AND _ready = B'1' AND _deleted = B'0'");
		
		$sth->bindParam (':id', $this->getValue (), PDO::PARAM_INT);
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (!$obj)
			return NULL;
		
		return array ('_NAME_' => $obj->_name,
					  '_SIZE_' => $obj->_size,
					  '_MIME_' => $obj->_mimetype);
	}
}