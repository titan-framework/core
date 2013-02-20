<?
/**
 * DatabaseMaker.php
 *
 * This class is used for generate code (SQL) from XML definition files
 * presents on instance sections.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage database
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 */
class DatabaseMaker
{
	private $file = '';
	
	private $primary = '';
	
	private $table = '';
	
	private $fields = array ();
	
	public function __construct ()
	{
		$args = func_get_args();
		
		$fileName = FALSE;
		
		foreach ($args as $trash => $arg)
		{
			if (!file_exists ($arg) || is_dir ($arg))
				continue;
			
			$fileName = $arg;
			
			break;
		}
		
		if ($fileName === FALSE)
			throw new Exception ('Arquivo XML não encontrado.');
		
		$xml = new Xml ($fileName);
		
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
	
	public function getSize ()
	{
		return sizeof ($this->fields);
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
	
	public function makeTable ()
	{
		$fields = array ();
		while ($field = $this->getField ())
			if (trim (self::toSql ($field)) != '')
				$fields [] = self::toSql ($field);
		
		$constraints = array ();
		while ($field = $this->getField ())
			if (trim (self::toConstraint ($field)) != '')
				$constraints [] = self::toConstraint ($field);
		
		$strFields = implode (", ", $fields);
		
		if (strpos ($strFields, '_user') === FALSE)
		{
			$fields [] = "_user INTEGER NOT NULL";
			$constraints [] = "CONSTRAINT ". str_replace (".", "_", $this->getTable ()) ."_user_fk FOREIGN KEY (_user) REFERENCES ". Database::singleton ()->getSchema () ."._user(_id) ON DELETE RESTRICT ON UPDATE CASCADE NOT DEFERRABLE";
		}
		
		if (strpos ($strFields, '_create') === FALSE)
			$fields [] = "_create TIMESTAMP WITHOUT TIME ZONE DEFAULT now() NOT NULL";
		
		if (strpos ($strFields, '_update') === FALSE)
			$fields [] = "_update TIMESTAMP WITHOUT TIME ZONE DEFAULT now() NOT NULL";
			
		return "CREATE TABLE ". $this->getTable () ." (\n  id SERIAL PRIMARY KEY,\n  ". implode (",\n  ", $fields) . (sizeof ($constraints) ? ",\n  ". implode (",\n  ", $constraints) : "") ."\n) WITHOUT OIDS; \n\n";
	}
	
	public function getDependencies ()
	{
		$deps = array ();
		while ($field = $this->getField ())
			if (method_exists ($field, 'getLink'))
				$deps [] = $field->getLink ();
		
		return $deps;
	}
	
	public function getGroup ()
	{
		$group = each ($this->groupsInfo);
		
		if ($group !== FALSE)
			return new Group ($group ['value']);
		
		reset ($this->groupsInfo);
		
		return NULL;
	}
	
	public static function toSql ($field)
	{
		if (!is_object ($field))
			return $field ." VARCHAR(256) DEFAULT NULL";
		
		$instance = Instance::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toDbMaker.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		return $field->getColumn () ." VARCHAR(256) DEFAULT NULL";
	}
	
	public static function toConstraint ($field)
	{
		if (!is_object ($field))
			return "";
		
		$instance = Instance::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toConstraint.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		return "";
	}
}
?>