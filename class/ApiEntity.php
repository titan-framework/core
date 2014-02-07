<?
/**
 * ApiEntity.php
 *
 * This class load XML definitions files and instanciate a API Entity artefact.
 * This class derivate (but not extends) the class Form for appliance on REST Like API.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage form
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see Form, Search
 * @todo Create Item class for array replace.
 */
class ApiEntity
{
	protected $file = '';

	protected $primary = '';
	
	protected $itemId = 0;
	
	protected $codeColumn = '';
	
	protected $code = NULL;

	protected $table = '';

	public $fields = array ();

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
	
	public function setId ($itemId)
	{
		$this->itemId = $itemId;
	}
	
	public function getCode ()
	{
		return $this->code;
	}
	
	public function setCode ($code)
	{
		$this->code = $code;
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
	
	public function recovery ($data = FALSE)
	{
		if (is_array ($data))
			foreach ($data as $key => $value)
			{
				if (is_string ($value))
					$data [$key] = $value;
			}
		else
			$data = $_POST;
		
		foreach ($this->fields as $assign => $field)
		{
			if ($field->isReadOnly ())
				continue;
			
			if (!array_key_exists ($field->getApiColumn (), $data) && !array_key_exists ($field->getApiColumn (), $_FILES))
			{
				if ($field->isRequired ())
					throw new ApiException (__ ('Required field [[1]] is missing or empty!', $field->getLabel ()), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);
				
				unset ($this->fields [$assign]);
				
				continue;
			}
			
			if (array_key_exists ($field->getApiColumn (), $_FILES))
				$value = $_FILES [$field->getApiColumn ()];
			else
				$value = $data [$field->getApiColumn ()];
			
			$this->fields [$assign]->setValue (self::fromApi ($this->fields [$assign], $value));
		}

		return TRUE;
	}
	
	public function load ($id, $ownerOnly = FALSE, $user = NULL)
	{
		$fields = array ();
			foreach ($this->fields as $assign => $field)
				if ($field->isLoadable ())
					$fields [] = Database::toSql ($field);
		
		if ($ownerOnly && (is_null ($user || !is_integer ($user) || !$user)))
			throw new Exception (__ ('The user must be acknowledged! Please, alert system responsible.'));
		
		$db = Database::singleton ();
		
		if ($this->useCode ())
		{
			if (is_null ($id) || trim ($id) == '')
				throw new Exception (__ ('Invalid code!'));
			
			$this->setCode ($id);
			
			if ($ownerOnly)
				$sql = "SELECT ". $this->getPrimary () .", ". implode (', ', $fields) ." FROM ". $this->getTable () ." WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn () ." AND _user = :_user";
			else
				$sql = "SELECT ". $this->getPrimary () .", ". implode (', ', $fields) ." FROM ". $this->getTable () ." WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn ();
			
			$sth = $db->prepare ($sql);
			
			$sth->bindParam (':'. $this->getCodeColumn (), $id, PDO::PARAM_STR);
		}
		else
		{
			if (is_null ($id) || !is_integer ($id) || !(int) $id)
				throw new Exception (__ ('Invalid identifier!'));
				
			if ($ownerOnly)
				$sql = "SELECT ". $this->getPrimary () .", ". implode (', ', $fields) ." FROM ". $this->getTable () ." WHERE ". $this->getPrimary () ." = :". $this->getPrimary () ." AND _user = :_user";
			else
				$sql = "SELECT ". $this->getPrimary () .", ". implode (', ', $fields) ." FROM ". $this->getTable () ." WHERE ". $this->getPrimary () ." = :". $this->getPrimary ();
			
			$sth = $db->prepare ($sql);
			
			$sth->bindParam (':'. $this->getPrimary (), $id, PDO::PARAM_INT);
		}
		
		if ($ownerOnly)
			$sth->bindParam (':_user', $user, PDO::PARAM_INT);
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if (!$obj)
			return FALSE;
		
		$pk = $this->getPrimary ();
		
		$itemId = $obj->$pk;
		
		$this->setId ($itemId);
		
		foreach ($this->fields as $assign => $field)
			if ($field->isLoadable ())
				$this->fields [$assign] = Database::fromDb ($field, $obj);
			elseif (isset ($itemId))
				$this->fields [$assign]->load ($itemId);

		reset ($this->fields);

		return TRUE;
	}
	
	public function save ($user = NULL, $id = NULL, $onlyLast = TRUE)
	{
		$fields = array ();
		$values = array ();
		$binds  = array ();
		
		$update = $this->getField ('_API_UPDATE_UNIX_TIMESTAMP_');
		
		if ($onlyLast && (is_null ($update) || !is_object ($update) || !$update->getUnixTime ()))
			throw new Exception (__ ('Has a problem with column of last change control! Please, alert system responsible.'));
		
		foreach ($this->fields as $key => $field)
		{
			if ($field->isReadOnly () || !$field->isSavable ())
				continue;
			
			$assign = $field->getAssign ();
			
			$fields [$assign] = $field->getColumn ();
			
			if ($field->getBind ())
			{
				$values [$assign] = ":". $field->getColumn ();
				$binds  [$assign] = Database::toBind ($field);
				$types  [$assign] = $field->getBindType ();
				
				if (method_exists ($field, 'getMaxLength'))
					$sizes [$assign] = (int) $field->getMaxLength ();
				else
					$sizes [$assign] = 0;
			}
			else
				$values [$assign] = Database::toValue ($field);
		}

		reset ($this->fields);

		if (!is_null ($user) && is_numeric ($user) && (int) $user)
		{
			array_push ($fields, '_user');
			array_push ($values, ':_user');
			array_push ($binds, (int) $user);
			array_push ($types, PDO::PARAM_INT);
			array_push ($sizes, 0);
		}

		// throw new Exception ($itemId);
		
		$db = Database::singleton ();
		
		$aux = array ();
		foreach ($fields as $key => $field)
			$aux [] = $field ." = ". $values [$key];
		
		if ($this->useCode ())
		{
			if (is_null ($id) || trim ($id) == '')
				throw new Exception (__ ('Invalid code!'));
			
			if ($onlyLast)
				$sqlUpdate = "UPDATE ". $this->getTable () ." SET ". implode (", ", $aux) ." WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn () ." AND ". $update->getUnixTime () ." > extract (epoch from ". $update->getColumn () .")::integer";
			else
				$sqlUpdate = "UPDATE ". $this->getTable () ." SET ". implode (", ", $aux) ." WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn ();
			
			$sthUpdate = $db->prepare ($sqlUpdate);
			
			$ins = array ();
			foreach ($fields as $key => $field)
				$ins [] = ":". $field ." AS ". $field;
			
			$sqlInsert = "INSERT INTO ". $this->getTable () ." (". $this->getCodeColumn () .", ". implode (", ", $fields) .") 
						  SELECT (:". $this->getCodeColumn () .")::varchar AS ". $this->getCodeColumn () .", ". implode (", ", $ins) ." WHERE NOT EXISTS (SELECT 1 FROM ". $this->getTable () ." WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn () .")";
			
			$sthInsert = $db->prepare ($sqlInsert);
			
			$sthUpdate->bindParam (':'. $this->getCodeColumn (), $id, PDO::PARAM_STR);
			$sthInsert->bindParam (':'. $this->getCodeColumn (), $id, PDO::PARAM_STR);
			
			foreach ($binds as $assign => $trash)
				if ($sizes [$assign] && $types [$assign] == PDO::PARAM_STR)
				{
					$sthUpdate->bindParam ($values [$assign], $binds [$assign], $types [$assign], $sizes [$assign]);
					$sthInsert->bindParam ($values [$assign], $binds [$assign], $types [$assign], $sizes [$assign]);
				}
				else
				{
					$sthUpdate->bindParam ($values [$assign], $binds [$assign], $types [$assign]);
					$sthInsert->bindParam ($values [$assign], $binds [$assign], $types [$assign]);
				}
			
			$sthUpdate->execute ();
			$sthInsert->execute ();
			
			$sth = $db->prepare ("SELECT ". $this->getPrimary () ." AS pk FROM ". $this->getTable () ." WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn ());
			
			$sth->bindParam (':'. $this->getCodeColumn (), $id, PDO::PARAM_STR);
			
			$sth->execute ();
			
			$itemId = (int) $sth->fetchColumn ();
			
			$this->setCode ($id);
		}
		else
			if (!is_null ($id) && is_numeric ($id) && (int) $id)
			{
				$id = (int) $id;
				
				if ($onlyLast)
					$sqlUpdate = "UPDATE ". $this->getTable () ." SET ". implode (", ", $aux) ." WHERE ". $this->getPrimary () ." = :". $this->getPrimary () ." AND ". $update->getUnixTime () ." > extract (epoch from ". $update->getColumn () .")::integer";
				else
					$sqlUpdate = "UPDATE ". $this->getTable () ." SET ". implode (", ", $aux) ." WHERE ". $this->getPrimary () ." = :". $this->getPrimary ();
				
				$sth = $db->prepare ($sqlUpdate);
				
				$sth->bindParam (':'. $this->getPrimary (), $id, PDO::PARAM_INT);
				
				foreach ($binds as $assign => $trash)
					if ($sizes [$assign] && $types [$assign] == PDO::PARAM_STR)
						$sth->bindParam ($values [$assign], $binds [$assign], $types [$assign], $sizes [$assign]);
					else
						$sth->bindParam ($values [$assign], $binds [$assign], $types [$assign]);
				
				$sth->execute ();
				
				$itemId = $id;
			}
			else
			{
				$id = (int) Database::nextId ($this->getTable (), $this->getPrimary ());
				
				$sth = $db->prepare ("INSERT INTO ". $this->getTable () ." (". $this->getPrimary () .", ". implode (", ", $fields) .") VALUES (:". $this->getPrimary () .", ". implode (", ", $values) .")");
				
				$sth->bindParam (':'. $this->getPrimary (), $id, PDO::PARAM_INT);
				
				foreach ($binds as $assign => $trash)
					if ($sizes [$assign] && $types [$assign] == PDO::PARAM_STR)
						$sth->bindParam ($values [$assign], $binds [$assign], $types [$assign], $sizes [$assign]);
					else
						$sth->bindParam ($values [$assign], $binds [$assign], $types [$assign]);
				
				$sth->execute ();
				
				$itemId = $id;
			}
		
		if (!isset ($itemId) || is_null ($itemId) || !$itemId)
			return FALSE;
		
		$this->setId ($itemId);

		Lucene::singleton ()->save ($itemId, $this->getResume ($itemId, TRUE));

		foreach ($this->fields as $key => $field)
			if (!$field->isSavable ())
				$field->save ($itemId);
		
		reset ($this->fields);
		
		return $itemId;
	}
	
	public function delete ($id = NULL, $permanent = TRUE, $ownerOnly = FALSE, $user = NULL)
	{
		if ($ownerOnly && (is_null ($user || !is_integer ($user) || !$user)))
			throw new Exception (__ ('The user must be acknowledged! Please, alert system responsible.'));
		
		$db = Database::singleton ();
		
		if ($this->useCode ())
		{
			if (is_null ($id) || trim ($id) == '')
				throw new Exception (__ ('Invalid code!'));
			
			if ($permanent)
				if ($ownerOnly)
					$sql = "DELETE FROM ". $this->getTable () ." WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn () ." AND _user = :_user";
				else
					$sql = "DELETE FROM ". $this->getTable () ." WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn ();
			else
				if ($ownerOnly)
					$sql = "UPDATE ". $this->getTable () ." SET _deleted = B'1' WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn () ." AND _user = :_user";
				else
					$sql = "UPDATE ". $this->getTable () ." SET _deleted = B'1' WHERE ". $this->getCodeColumn () ." = :". $this->getCodeColumn ();
			
			$sth = $db->prepare ($sql);
			
			$sth->bindParam (':'. $this->getCodeColumn (), $id, PDO::PARAM_STR);
		}
		else
		{
			if (is_null ($id) || !is_integer ($id) || !(int) $id)
				throw new Exception (__ ('Invalid identifier!'));
			
			if ($permanent)
				if ($ownerOnly)
					$sql = "DELETE FROM ". $this->getTable () ." WHERE ". $this->getPrimary () ." = :". $this->getPrimary () ." AND _user = :_user";
				else
					$sql = "DELETE FROM ". $this->getTable () ." WHERE ". $this->getPrimary () ." = :". $this->getPrimary ();
			else
				if ($ownerOnly)
					$sql = "UPDATE ". $this->getTable () ." SET _deleted = B'1' WHERE ". $this->getPrimary () ." = :". $this->getPrimary () ." AND _user = :_user";
				else
					$sql = "UPDATE ". $this->getTable () ." SET _deleted = B'1' WHERE ". $this->getPrimary () ." = :". $this->getPrimary ();
			
			$sth = $db->prepare ($sql);
			
			$sth->bindParam (':'. $this->getPrimary (), $id, PDO::PARAM_INT);
		}
		
		if ($ownerOnly)
			$sth->bindParam (':_user', $user, PDO::PARAM_INT);
		
		if (!$sth->execute ())
			return FALSE;

		Lucene::singleton ()->delete ($itemId);

		return TRUE;
	}
	
	public function getResume ($friendly = FALSE)
	{
		$resume = "";

		if (!$friendly)
		{
			$resume .= "[ID# ". $this->getId () ." / CODE: ". $this->getCode () ." ] \n\n";
		}
		
		while ($field = $this->getField (FALSE))
			$resume .= (trim ($field->getLabel ()) != '' ? $field->getLabel () .": \n" : '') . Form::toText ($field) ." \n\n";
		
		reset ($this->fields);

		return $resume;
	}
	
	public function json ()
	{
		$itemId = $this->getId ();
		
		$object = array ();
		
		if ($this->useCode ())
			$object [$this->getCodeColumn ()] = $this->getCode ();
		else
			$object [$this->getPrimary ()] = $itemId;
		
		while ($field = $this->getField ())
			$object [$field->getApiColumn ()] = ApiEntity::toApi ($field, $itemId);
		
		return json_encode ((object) $object);
	}
	
	public static function toApi ($field, $itemId = NULL)
	{
		if (!is_object ($field))
			return $field;

		$instance = Instance::singleton ();

		$fieldId = 'field_'. $field->getAssign ();

		$db = Database::singleton ();
		
		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toApi.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);
		
		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toText.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toHtml.php';

			if (file_exists ($file))
				return strip_tags (include $file);

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return strip_tags ($field->getValue ());
	}
	
	public static function fromApi ($field, $value)
	{
		if (!is_object ($field))
			return $value;

		$instance = Instance::singleton ();
		
		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'fromApi.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);
		
		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'fromForm.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return $value;
	}
}
?>