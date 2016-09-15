<?php
/**
 * Search.php
 *
 * This class load XML definitions files for search forms and instanciate
 * a search artefact for list.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage form
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see View, Search
 */
class Search
{
	protected $file = '';

	protected $primary = '';

	protected $table = '';

	protected $father = FALSE;

	protected $fields = array ();

	protected $blocked = array ();

	protected $cookie;

	protected $hash = '74770ea6b171e03791f9f388cecd74bc';

	protected $timeout = 0;

	const TCLEAR  = 0;
	const TSEARCH = 1;

	public function __construct ()
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);

		$action = Business::singleton ()->getAction (Action::TCURRENT);

		$args = func_get_args();

		$fileName = FALSE;

		if (is_object ($action) && $action->getXmlPath () !== FALSE && trim ($action->getXmlPath ()) != '')
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

			if (!isset ($array ['search'][0]))
				throw new Exception ('A tag &lt;search&gt;&lt;/search&gt; não foi encontrada no XML ['. $fileName .']!');

			xmlCache ($cacheFile, $array);
		}

		$array = $array ['search'][0];

		$this->file = $fileName;

		if (array_key_exists ('table', $array))
			$this->table = $array ['table'];

		if (array_key_exists ('father', $array) && isset ($_GET['fatherId']) && (int) $_GET['fatherId'])
		{
			$_POST['search'] = self::TSEARCH;
			
			$_POST ['search_'. $array ['father']] = $_GET['fatherId'];
			
			$this->father = TRUE;
		}
		
		$search = Instance::singleton ()->getSearch ();

		if (array_key_exists ('hash', $search))
			$this->hash = $search ['hash'];

		if (array_key_exists ('timeout', $search))
			$this->timeout = $search ['timeout'];

		$section = Business::singleton ()->getSection (Section::TCURRENT);

		$action = Business::singleton ()->getAction (Action::TCURRENT);

		$this->cookie = md5 (User::singleton ()->getId () .'-'. $this->getHash () .'-'. $file);

		if (isset ($_POST['search']) && $_POST['search'] == self::TCLEAR)
		{
			setcookie ($this->cookie, '', time() - 3600);

			unset ($_COOKIE [$this->cookie]);
		}

		$cookie = array ();

		if (isset ($_COOKIE [$this->cookie]) && $this->father === FALSE)
			$cookie = unserialize (base64_decode ($_COOKIE [$this->cookie]));

		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
				if ($obj = Type::factory ($this->getTable (), $field))
				{
					if (array_key_exists ($obj->getAssign (), $cookie) && !is_object ($cookie [$obj->getAssign ()]))
						$obj->setValue ($cookie [$obj->getAssign ()]);

					$this->fields [$obj->getAssign ()] = $obj;
				}
		
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

	public function getHash ()
	{
		return $this->hash;
	}

	public function getTimeout ()
	{
		return $this->timeout;
	}

	public function getFields ()
	{
		return $this->fields;
	}

	public function setFieldValue ($id, $value)
	{
		$this->fields [$id]->setValue ($value);
	}

	public function addBlock ($id, $value)
	{
		$this->fields [$id]->setValue ($value);

		$this->blocked [] = $id;
	}

	public function isBlocked ($id)
	{
		if (is_object ($id))
			$id = $id->getAssign ();

		return in_array ($id, $this->blocked);
	}

	public function recovery ()
	{
		if (!isset ($_POST['search']) || $_POST['search'] != self::TSEARCH)
			return FALSE;

		$formData = $_POST;

		$cookie = array ();

		foreach ($this->fields as $assign => $trash)
			if (array_key_exists ('search_'. $assign, $formData))
			{
				$value = Search::fromForm ($this->fields [$assign], $formData ['search_'. $assign]);

				$this->fields [$assign]->setValue ($value);

				if (!$this->fields [$assign]->isEmpty ())
					$cookie [$assign] = $value;
			}

		reset ($this->fields);

		setcookie ($this->cookie, base64_encode (serialize ($cookie)), time () + (int) $this->getTimeout ());

		return TRUE;
	}

	public function makeWhere ()
	{
		$aux = array ();
		foreach ($this->fields as $trash => $field)
			if (!$field->isEmpty ())
				$aux [] = self::toWhere ($field);

		reset ($this->fields);

		//throw new Exception (implode (' AND ', $aux));

		return implode (' AND ', $aux);
	}

	public function isEmpty ()
	{
		foreach ($this->fields as $trash => $field)
			if (!$field->isEmpty ())
			{
				reset ($this->fields);

				return FALSE;
			}

		reset ($this->fields);

		return TRUE;
	}

	public function getField ($assign = FALSE)
	{
		if ($assign !== FALSE)
			if (array_key_exists ($assign, $this->fields))
				return $this->fields [$assign];
			else
				return current ($this->fields);

		$field = each ($this->fields);

		if ($field !== FALSE)
			return $field ['value'];

		reset ($this->fields);

		return NULL;
	}

	public static function toForm ($field)
	{
		if (!is_object ($field))
			return '<input type="text" class="field" name="'. $field .'" value="" />';

		$fieldName = 'search_'. $field->getAssign ();

		$fieldId = 'field_'. $field->getAssign ();

		$instance = Instance::singleton ();

		$db = Database::singleton ();

		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toSearch.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toForm.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return '<input type="text" class="field" name="'. $fieldName .'" id="'. $fieldId .'" value="'. $field->getValue () .'" />';
	}
	
	public static function fromForm ($field, $value)
	{
		if (!is_object ($field))
			return $value;

		$instance = Instance::singleton ();

		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'fromSearch.php';

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

	public static function toWhere ($field)
	{
		if (!is_object ($field))
			return $field;

		$instance = Instance::singleton ();

		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toWhere.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return $field->getTable () .'.'. $field->getColumn () ." = ". Database::toValue ($field);
	}
	
	public static function toHtml ($field)
	{
		if (!is_object ($field))
			return $field;

		$instance = Instance::singleton ();

		$fieldId = 'field_'. $field->getAssign ();

		$db = Database::singleton ();

		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toChoose.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toHtml.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return $field->getValue ();
	}
}
?>