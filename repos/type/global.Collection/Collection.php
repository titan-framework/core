<?
class Collection extends Type
{
	protected $xmlPath = '';
		
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setLoadable (FALSE);
		
		$this->setSavable (FALSE);
		
		$this->setFullWidth (TRUE);
		
		$this->setSubmittable (FALSE);
		
		if (array_key_exists ('xml-path', $field))
			$this->setXmlPath ($field ['xml-path']);
	}
	
	public function getXmlPath ()
	{
		return $this->xmlPath;
	}
	
	public function setXmlPath ($xmlPath)
	{
		$this->xmlPath = $xmlPath;
	}
	
	public function getSize ()
	{
		global $itemId;
		
		$form = new Form ($this->getXmlPath ());
		
		$query = Database::singleton ()->query ("SELECT COUNT(*) FROM ". $form->getTable () ." WHERE ". $this->getColumn () ." = '". $itemId ."'");
		
		return (int) $query->fetchColumn ();
	}
	
	public function copy ($itemId, $newId)
	{
		if (!(int) $itemId || !(int) $newId || $itemId == $newId)
			throw new Exception (__ ('Impossible to copy field [[1]]! Data losted.', $this->getLabel () .' ('. $this->getColumn () .')'));
		
		$form = new Form ($this->getXmlPath ());
		
		$fields = array ();
		
		while ($field = $form->getField ())
			if ($field->isSavable ())
				$fields [] = $field->getColumn ();
		
		$sql = "INSERT INTO ". $form->getTable () ." (". $this->getColumn () .", ". implode (", ", $fields) .") 
				SELECT '". $newId ."' AS ". $this->getColumn () .", ". implode (", ", $fields) ." FROM ". $form->getTable () ." WHERE ". $this->getColumn () ." = '". $itemId ."'";
		
		Database::singleton ()->exec ($sql);
		
		return TRUE;
	}
}
?>