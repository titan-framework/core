<?
class Multiply extends Select
{
	protected $value = array ();
	
	protected $relation = '';
	
	protected $forGraph = FALSE;
	
	protected $checkBox = FALSE;
	
	protected $primary = '';
	
	protected $relationLink = '';
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setLoadable (FALSE);
		
		$this->setSavable (FALSE);
		
		if (array_key_exists ('relation', $field))
			$this->setRelation ($field ['relation']);
		
		if (array_key_exists ('primary', $field))
			$this->setPrimary ($field ['primary']);
		
		if (array_key_exists ('check-box', $field))
			$this->checkBox = strtoupper (trim ($field ['check-box'])) == 'TRUE' ? TRUE : FALSE;
		
		if (array_key_exists ('relation-link', $field) && trim ($field ['relation-link']) != '')
			$this->relationLink = trim ($field ['relation-link']);
		else
			$this->relationLink = array_pop ((explode ('.', $this->getTable ())));
	}
	
	public function setValue ($value)
	{
		if (!is_array ($value))
			$value = array ($value);
		
		$this->value = $value;
	}
	
	public function getRelation ()
	{
		return $this->relation;
	}
	
	public function setRelation ($relation)
	{
		$this->relation = trim ($relation);
	}
	
	public function getRelationLink ()
	{
		return $this->relationLink;
	}
	
	public function getPrimary ()
	{
		return $this->primary;
	}
	
	public function setPrimary ($primary)
	{
		$this->primary = trim ($primary);
	}
	
	public function isEmpty ()
	{
		$value = $this->getValue ();
		
		if (!sizeof ($value))
			return TRUE;
		
		return FALSE;
	}
	
	public function save ($id)
	{
		$message = Message::singleton ();
		
		$db = Database::singleton ();
		
		$array = array_unique ($this->getValue ());
		
		try
		{
			$db->beginTransaction ();
			
			$db->exec ("DELETE FROM ". $this->getRelation () ." WHERE ". $this->getRelationLink () ." = '". $id ."'");
			
			$sth = $db->prepare ("INSERT INTO ". $this->getRelation () ." (". $this->getRelationLink () .", ". $this->getColumn () .") VALUES ('". $id ."', :link)");
			
			foreach ($array as $trash => $linkId)
				$sth->execute (array (':link' => $linkId));
			
			$db->commit ();
		}
		catch (Exception $e)
		{
			$db->rollBack ();
			
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$db->rollBack ();
			
			$message->addWarning ($e->getMessage ());
		}
	}
	
	public function load ($id)
	{
		$message = Message::singleton ();
		
		$db = Database::singleton ();
		
		try
		{
			$sql = "SELECT DISTINCT ". $this->getColumn () ." FROM ". $this->getRelation () ." WHERE ". $this->getRelationLink () ." = '". $id ."'";
			
			$sth = $db->query ($sql);
			
			$this->setValue ($sth->fetchAll (PDO::FETCH_COLUMN));
		}
		catch (PDOException $e)
		{	
			toLog ($e->getMessage ());
		}
	}
	
	public function copy ($itemId, $newId)
	{
		if (!(int) $itemId || !(int) $newId || $itemId == $newId)
			throw new Exception (__ ('Impossible to copy field [[1]]! Data losted.', $this->getLabel () .' ('. $this->getColumn () .')'));
		
		$cLocl = $this->getRelationLink ();
		$cLink = $this->getColumn ();
		
		$sql = "INSERT INTO ". $this->getRelation () ." (". $cLocl .", ". $cLink .")
				SELECT '". $newId ."' AS ". $cLocl .", ". $cLink ."
				FROM ". $this->getRelation () ."
				WHERE ". $cLocl ." = '". $itemId ."' AND ". $cLink ." IS NOT NULL";
		
		Database::singleton ()->exec ($sql);
		
		return TRUE;
	}
	
	public function useCheckBoxes ()
	{
		return $this->checkBox;
	}
	
	static public function cleanValues (&$item, $key)
	{
		if (trim ((string) $item) == '')
			$item = 0;
	}
}
?>