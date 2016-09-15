<?php
class MakeSql
{
	private $file = '';
	
	private $primary = '';
	
	private $table = '';
	
	private $fields = array ();
	
	public function __construct ()
	{
		global $section;
		
		$args = func_get_args();
		
		$fileName = FALSE;
		
		foreach ($args as $trash => $arg)
		{
			if (!file_exists ('section/'. $section->getName () .'/'. $arg))
				continue;
			
			$fileName = $arg;
			
			break;
		}
		
		if ($fileName === FALSE)
			throw new Exception ('Arquivo XML não encontrado em [section/'. $section->getName () .'/].');
		
		$xml = new Xml ('section/'. $section->getName () .'/'. $fileName);
		
		$array = $xml->getArray ();
		
		if (!isset ($array ['form'][0]))
			throw new Exception ('A tag &lt;form&gt;&lt;/form&gt; não foi encontrada no XML ['. $fileName .']!');
		
		$array = $array ['form'][0];
		
		$this->file = $fileName;
		
		if (array_key_exists ('table', $array))
			$this->table = $array ['table'];
		
		if (array_key_exists ('primary', $array))
			$this->primary = $array ['primary'];
		
		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
				if ($obj = Type::factory ($this->getTable (), $field))
					$this->fields [$obj->getAssign ()] = $obj;
		
		if (array_key_exists ('group', $array) && is_array ($array ['group']))
			foreach ($array ['group'] as $trash => $group)
				if (array_key_exists ('field', $group) && is_array ($group ['field']))
					foreach ($group ['field'] as $trash => $field)
						if ($obj = Type::factory ($this->getTable (), $field))
							$this->fields [$obj->getAssign ()] = $obj;
		
		reset ($this->fields);
	}
	
	public function getFile ()
	{
		return $this->file;
	}
	
	public function getTable ()
	{
		return $this->table;
	}
	
	public function getPrimary ()
	{
		return $this->primary;
	}
	
	public function getFields ()
	{
		return $this->fields;
	}
	
	public function getUniques ()
	{
		$uniques = array ();
		
		foreach ($this->fields as $key => $field)
			if ($field->isUnique ())
				$uniques [$key] = $field;
		
		return $uniques;
	}
	
	public function getRequireds ()
	{
		$requireds = array ();
		
		foreach ($this->fields as $key => $field)
			if ($field->isRequired ())
				$requireds [$key] = $field;
		
		return $requireds;
	}
	
	public function recovery ($formData = FALSE)
	{
		if (!is_array ($formData))
			$formData = $_POST;
		
		foreach ($formData as $assign => $value)
			if (array_key_exists ($assign, $this->fields))
			{
				$value = self::fromForm ($this->fields [$assign], $value);
				
				$this->fields [$assign]->setValue ($value);
			}
		
		return TRUE;
	}
	
	public function execute ($itemId = 0)
	{
		$fields = array ();
		
		if ($itemId)
		{
			foreach ($this->fields as $key => $field)
				if (!$field->isReadOnly ())
					$fields [] = $field->getColumn () ." = ". Database::toValue ($field);
			
			$sql = "UPDATE ". $this->getTable () ." SET ". implode (", ", $fields) ." WHERE ". $this->getPrimary () ." = '". $itemId ."'";
		}
		else
		{
			foreach ($this->fields as $key => $field)
				if (!$field->isReadOnly ())
				{
					$fields [] = $field->getColumn ();
					$values [] = Database::toValue ($field);
				}
			
			$sql = "INSERT INTO ". $this->getTable () ." (". implode (", ", $fields) .") VALUES (". implode (", ", $values) .")";
		}
		//throw new Exception ($sql);
		$db = Database::singleton ();
		
		$sth = $db->prepare ($sql);
		
		return $sth->execute ();
	}
	
	public function delete ($itemId = 0, $permanent = TRUE)
	{	
		if (!$itemId)
			return FALSE;
		
		if ($permanent)
			$sql = "DELETE FROM ". $this->getTable () ." WHERE ". $this->getPrimary () ." = '". $itemId ."'";
		else
			$sql = "UPDATE ". $this->getTable () ." SET apagado = '1' WHERE ". $this->getPrimary () ." = '". $itemId ."'";
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ($sql);
		
		return $sth->execute ();
	}
	
	public function getField ($group = FALSE)
	{
		$field = each ($this->fields);
		
		while ($field !== FALSE)
		{
			if ($group === FALSE || (array_key_exists ($group, $this->groups) && in_array ($field ['value']->getAssign (), $this->groups [$group])))	
				return $field ['value'];
			
			$field = each ($this->fields);
		}
		
		reset ($this->fields);
		
		return NULL;
	}
	
	public function getGroup ()
	{
		$group = each ($this->groupsInfo);
		
		if ($group !== FALSE)
			return new Group ($group ['value']);
		
		reset ($this->groupsInfo);
		
		return NULL;
	}
	
	public static function toSqlScript ($field)
	{
		if (!is_object ($field))
			return $field .' character varying(256)';
		
		$instance = Instance::singleton ();
		
		if (!file_exists ($instance->getReposPath () .'type/global.'. get_class ($field) .'/'. get_class ($field) .'.toSqlScript.php'))
			return $field->getColumn () .' character varying(256)';
		
		$db = Database::singleton ();
		
		return include $instance->getReposPath () .'type/global.'. get_class ($field) .'/'. get_class ($field) .'.toSqlScript.php';
	}
}
?>