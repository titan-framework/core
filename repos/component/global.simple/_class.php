<?
class FormSimple extends Form
{
	static private $forms = array ();
	
	static public function singleton ()
	{
		$files = func_get_args();
		
		$class = __CLASS__;
		
		$form = new $class ($files);
		
		if (array_key_exists ($form->getAssign (), self::$forms))
			return self::$forms [$form->getAssign ()];
		
		self::$forms [$form->getAssign ()] =& $form;
		
		return $form;
	}
	
	public function load ($id)
	{
		if ($this->isLoaded ())
			return TRUE;
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("SELECT _content FROM _simple WHERE _id = '". $id ."'");
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (!$obj)
		{
			$sth = $db->prepare ("INSERT INTO _simple (_id, _content, _user) VALUES ('". $id ."', '', '". User::singleton ()->getId () ."')");
			
			return $sth->execute ();
		}
		
		if (trim ($obj->_content) == '')
			return TRUE;
		
		$fields = unserialize (base64_decode ($obj->_content));
		
		foreach ($fields as $assign => $value)
			if (array_key_exists ($assign, $this->fields))
				$this->fields [$assign]->setValue ($value);
		
		$this->setLoad ();
		
		return TRUE;
	}
	
	public function save ($id)
	{
		$fields = array ();
		
		foreach ($this->fields as $key => $field)
			$fields [$field->getAssign ()] = $field->getValue ();
		
		reset ($this->fields);
		
		$content = base64_encode (serialize ($fields));
		
		$sql = "UPDATE _simple SET _content = '". $content ."', _user = '". User::singleton ()->getId () ."', _update_date = NOW() WHERE _id = '". $id ."'";
		
		//throw new Exception ($sql);
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ($sql);
		
		return $sth->execute ();
	}
}
?>