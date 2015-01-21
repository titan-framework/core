<?php

class Note extends Type
{
	protected $value = array ();
	
	protected $relation;
	
	protected $columnEntity;
	
	protected $columnNote;
	
	protected $forGraph = FALSE;
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setLoadable (FALSE);
		
		$this->setSavable (FALSE);
		
		if (array_key_exists ('relation', $field))
			$this->setRelation ($field ['relation']);
		else
		{
			$entities = explode ('.', $this->getTable ());
			
			if (sizeof ($entities) > 1)
				$this->setRelation ($entities [0] .'.'. $entities [1] .'_note');
			else
				$this->setRelation ($entities [0] .'_note');
		}
			
		
		if (array_key_exists ('relation-entity', $field) && trim ($field ['relation-entity']) != '')
			$this->setColumnEntity ($field ['relation-entity']);
		else
			$this->setColumnEntity (array_pop (explode ('.', $this->getTable ())));
		
		if (array_key_exists ('relation-note', $field) && trim ($field ['relation-note']) != '')
			$this->setColumnNote ($field ['relation-note']);
		else
			$this->setColumnNote ('note');
	}
	
	public function setRelation ($relation)
	{
		$this->relation = trim ($relation);
	}
	
	public function getRelation ()
	{
		return $this->relation;
	}
	
	public function setColumnEntity ($column)
	{
		$this->columnEntity = trim ($column);
	}
	
	public function getColumnEntity ()
	{
		return $this->columnEntity;
	}
	
	public function setColumnNote ($column)
	{
		$this->columnNote = trim ($column);
	}
	
	public function getColumnNote ()
	{
		return $this->columnNote;
	}
	
	public function isEmpty ()
	{
		if (sizeof ($this->getValue ()))
			return FALSE;
		
		return TRUE;
	}
	
	public function save ($id)
	{
		if (!is_integer ($id) || !$id)
			throw new Exception (__ ('Invalid parameter!'));
		
		$db = Database::singleton ();
		
		$user = User::singleton ()->getId ();
		
		$sql = "INSERT INTO _note (_code, _user) 
				SELECT :code::VARCHAR AS _code, :user AS _user 
				WHERE NOT EXISTS (SELECT 1 FROM _note WHERE _code = :code)";
		
		$sth = $db->prepare ($sql);
		
		foreach ($this->getValue () as $trash => $code)
		{
			$sth->bindParam (':code', $code, PDO::PARAM_STR);
			$sth->bindParam (':user', $user, PDO::PARAM_INT);
			
			$sth->execute ();
		}
		
		try
		{
			$db->beginTransaction ();
			
			$sth = $db->prepare ("UPDATE ". $this->getRelation () ." SET _unlinked = B'1' WHERE ". $this->getColumnEntity () ." = :id");
			
			$sth->bindParam (':id', $id, PDO::PARAM_INT);
			
			$sth->execute ();
			
			$sqlInsert = "INSERT INTO ". $this->getRelation () ." (". $this->getColumnEntity () .", ". $this->getColumnNote () .", _unlinked)
						  SELECT :id AS ". $this->getColumnEntity () .", _id AS ". $this->getColumnNote () .", B'0' AS _unlinked FROM _note
						  WHERE _code = :code AND
						  NOT EXISTS (SELECT 1 FROM ". $this->getRelation () ." r
						  JOIN _note n ON n._id = r.". $this->getColumnNote () ."
						  WHERE n._code = :code AND r.". $this->getColumnEntity () ." = :id)";
			
			$sqlUpdate = "UPDATE ". $this->getRelation () ." r SET _unlinked = B'0' FROM _note n
						  WHERE n._id = r.". $this->getColumnNote () ." AND n._code = :code AND r.". $this->getColumnEntity () ." = :id";
			
			$sthInsert = $db->prepare ($sqlInsert);
			
			$sthUpdate = $db->prepare ($sqlUpdate);
			
			foreach ($this->getValue () as $trash => $code)
			{
				$sthInsert->bindParam (':id', $id, PDO::PARAM_INT);
				$sthInsert->bindParam (':code', $code, PDO::PARAM_STR);
			
				$sthInsert->execute ();
				
				$sthUpdate->bindParam (':id', $id, PDO::PARAM_INT);
				$sthUpdate->bindParam (':code', $code, PDO::PARAM_STR);
			
				$sthUpdate->execute ();
			}
			
			$db->commit ();
		}
		catch (Exception $e)
		{
			$db->rollBack ();
			
			throw $e;
		}
		catch (PDOException $e)
		{
			$db->rollBack ();
			
			throw $e;
		}
	}
	
	public function load ($id)
	{
		if (!is_numeric ($id) || !(int) $id)
			throw new Exception (__ ('Invalid parameter!'));
		
		$db = Database::singleton ();
		
		$sql = "SELECT n._code AS code FROM _note n
				JOIN ". $this->getRelation () ." r ON r.". $this->getColumnNote () ." = n._id
				WHERE ". $this->getColumnEntity () ." = :id";
		
		$sth = $db->prepare ($sql);
		
		$sth->bindParam (':id', $id, PDO::PARAM_INT);
		
		$sth->execute ();
		
		$this->setValue ($sth->fetchAll (PDO::FETCH_COLUMN, 0));
	}
}