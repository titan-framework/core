<?php
/**
 * ApiList.php
 *
 * This class load XML definitions files and instanciate a API List artefact.
 * This class derivate (but not extends) the class View for appliance on REST Like API.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage form
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see Form, Search
 * @todo Create Item class for array replace.
 */
class ApiList
{
	protected $file = '';

	protected $primary = '';
	
	protected $itemId = 0;
	
	protected $codeColumn = '';
	
	protected $code = NULL;

	protected $table = '';

	public $fields = array ();

	protected $sth = NULL;

	protected $sql = FALSE;

	protected $where = '';

	public function __construct ()
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);

		$action = Business::singleton ()->getAction (Action::TCURRENT);

		$args = func_get_args();

		$fileName = FALSE;

		if ($action->getXmlPath () !== FALSE && trim ($action->getXmlPath ()) != '')
			array_unshift ($args, $action->getXmlPath ());

		foreach ($args as $trash => $arg)
		{
			if (!file_exists ('section/'. $section->getName () .'/'. $arg))
				continue;

			$fileName = $arg;

			break;
		}

		if ($fileName === FALSE)
			throw new Exception ('Arquivo XML não encontrado em [section/'. $section->getName () .'/].');

		$file = 'section/'. $section->getName () .'/'. $fileName;
		
		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';

		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);

			$array = $xml->getArray ();

			if (!isset ($array ['api'][0]))
				throw new Exception ('A tag &lt;api&gt;&lt;/api&gt; não foi encontrada no XML ['. $fileName .']!');

			xmlCache ($cacheFile, $array);
		}
		
		if (!array_key_exists ('api', $array))
			throw new Exception ('Invalid XML View file [section/'. $section->getName () .'/].');
		
		$array = $array ['api'][0];

		$this->file = $fileName;

		if (array_key_exists ('table', $array))
			$this->table = trim ($array ['table']);

		if (array_key_exists ('primary', $array))
			$this->primary = trim ($array ['primary']);
		
		if (array_key_exists ('code', $array))
			$this->codeColumn = trim ($array ['code']);
		
		$user = User::singleton ();

		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
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

	public function setTable ($table)
	{
		$this->table = $table;
	}

	public function getPrimary ()
	{
		return $this->primary;
	}
	
	public function getCodeColumn ()
	{
		return $this->codeColumn;
	}
	
	public function useCode ()
	{
		return $this->getCodeColumn () != '';
	}

	public function getFields ()
	{
		return $this->fields;
	}

	public function getId ()
	{
		return $this->itemId;
	}
	
	public function getCode ()
	{
		return $this->code;
	}

	public function getSth ()
	{
		return $this->sth;
	}

	public function getSql ()
	{
		return $this->sql;
	}

	public function load ($where = '', $sql = FALSE)
	{
		if ($sql === FALSE)
		{
			$fields = array ();
			
			foreach ($this->fields as $assign => $field)
				if ($field->isLoadable ())
					$fields [] = Database::toSql ($field);
			
			$sql = "SELECT ". $this->getTable () .".". $this->getPrimary () .", ". ($this->getCodeColumn () == '' ? "" : $this->getTable () .".". $this->getCodeColumn () .", ") . (sizeof ($fields) ? implode (", ", $fields) : "*") ." FROM ". $this->getTable () . (trim ($where) != '' ? " WHERE ". $where : "");

			reset ($this->fields);
		}

		// throw new Exception ($sql);

		$db = Database::singleton ();

		$this->sth = $db->prepare ($sql);

		$this->sth->execute ();
		
		$this->sql = $sql;

		$this->where = $where;

		return TRUE;
	}

	public function getField ($assign = FALSE)
	{
		if ($assign !== FALSE)
			if (array_key_exists ($assign, $this->fields))
				return $this->fields [$assign];
			else
				return NULL;

		$field = each ($this->fields);

		if ($field !== FALSE)
			return $field ['value'];

		reset ($this->fields);

		return NULL;
	}

	public function getItem ()
	{
		if ($this->sth === NULL)
			return NULL;

		$obj = $this->sth->fetch (PDO::FETCH_OBJ);

		if (!$obj)
			return NULL;

		$primary = $this->getPrimary ();
		
		$this->itemId = $obj->$primary;
		
		if ($this->getCodeColumn() != '')
		{
			$code = $this->getCodeColumn ();
			
			$this->code = $obj->$code;
		}
		
		foreach ($this->fields as $assign => $field)
			if ($field->isLoadable ())
				$this->fields [$assign] = Database::fromDb ($field, $obj);
			else
				$this->fields [$assign]->load ($this->itemId);

		reset ($this->fields);

		return $obj;
	}
}
?>