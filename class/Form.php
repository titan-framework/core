<?php
/**
 * Form.php
 *
 * This class load XML definitions files and instanciate a form artefact.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage form
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see View, Search
 */
class Form
{
	/**
	 * This variable assure the existance of a unique Form object instance for
	 * each form artefact in instance.
	 *
	 * @var Form array
	 * @access private
	 * @static
	 */
	static private $forms = array ();

	/**
	 * Form unique indentification.
	 *
	 * @var string
	 * @access protected
	 */
	protected $assign = NULL;

	/**
	 * XML file name on section path.
	 *
	 * @var string
	 * @access protected
	 */
	protected $file = '';

	/**
	 * Name of table primary key.
	 *
	 * @var string
	 * @access protected
	 */
	protected $primary = '';

	/**
	 * Name of DB table.
	 *
	 * @var string
	 * @access protected
	 */
	protected $table = '';

	/**
	 * Navigation definition array.
	 * Control layer.
	 *
	 * @var array
	 * @access protected
	 */
	protected $go = array ();

	/**
	 * Loaded fields from XML.
	 *
	 * @var Type array
	 * @access protected
	 */
	protected $fields = array ();

	/**
	 * Fields in groups mapping array.
	 *
	 * @var array
	 * @access protected
	 */
	protected $groups = array ();

	/**
	 * Loaded fields groups definitions.
	 *
	 * @var array
	 * @access protected
	 */
	protected $groupsInfo = array ();

	/**
	 * Specify if form is loaded.
	 *
	 * @var boolean
	 * @access protected
	 */
	protected $loaded = FALSE;

	/**
	 * Actual ID value for loaded DB object.
	 *
	 * @var integer
	 * @access protected
	 */
	protected $itemId = 0;

	/**
	 * Class constructor.
	 * Designed for use in singleton or direct access.
	 * In singleton the unique instance of object will be guaranteed by
	 * assign and forms array variable.
	 *
	 * @see singleton ()
	 */
	public function __construct ($files)
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);

		$action = Business::singleton ()->getAction (Action::TCURRENT);

		$fileName = FALSE;

		if (!is_array ($files))
			$files = array ($files);

		foreach ($files as $trash => $file)
		{
			if (!file_exists ('section/'. $section->getName () .'/'. $file) || is_dir ('section/'. $section->getName () .'/'. $file))
				continue;

			$fileName = $file;

			break;
		}

		if ($fileName === FALSE)
			throw new Exception ('XML file not found in [section/'. $section->getName () .'/].');

		$file = 'section/'. $section->getName () .'/'. $fileName;

		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';

		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);

			$array = $xml->getArray ();

			if (!isset ($array ['form'][0]))
				throw new Exception ('A tag &lt;form&gt;&lt;/form&gt; não foi encontrada no XML ['. $fileName .']!');

			xmlCache ($cacheFile, $array);
		}

		if (!array_key_exists ('form', $array))
			throw new Exception ('Invalid XML Form file [section/'. $section->getName () .'/].');

		$array = $array ['form'][0];

		$this->assign = md5 ($section->getName () .'.'. $action->getName () .'.'. $fileName);

		$this->file = $fileName;

		if (array_key_exists ('table', $array))
			$this->table = $array ['table'];

		if (array_key_exists ('primary', $array))
			$this->primary = $array ['primary'];

		if (array_key_exists ('go-to', $array) && is_array ($array ['go-to']))
			foreach ($array ['go-to'] as $trash => $go)
			{
				if (!array_key_exists ('flag', $go) || !array_key_exists ('action', $go))
					continue;

				$this->go [$go ['flag']] = $go ['action'];
			}

		$user = User::singleton ();

		$groupId = 0;

		$this->groupsInfo [$groupId] = array ();

		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
				if ($obj = Type::factory ($this->getTable (), $field))
				{
					while ($perm = $obj->getRestrict ())
						if (!$user->hasPermission ($perm))
							continue 2;

					$this->fields [$obj->getAssign ()] = $obj;
					$this->groups [$groupId][] = $obj->getAssign ();
				}

		if (array_key_exists ('group', $array) && is_array ($array ['group']))
			foreach ($array ['group'] as $trash => $group)
			{
				$groupId++;

				if (array_key_exists ('label', $group))
					$label = $group ['label'];
				else
					$label = '';

				if (array_key_exists ('display', $group))
					$display = $group ['display'];
				else
					$display = 'visible';

				if (array_key_exists ('info', $group))
					$info = $group ['info'];
				else
					$info = '';

				$this->groupsInfo [$groupId] = array ($groupId, $label, $display, $info);

				if (array_key_exists ('field', $group) && is_array ($group ['field']))
					foreach ($group ['field'] as $trash => $field)
						if ($obj = Type::factory ($this->getTable (), $field))
						{
							while ($perm = $obj->getRestrict ())
								if (!$user->hasPermission ($perm))
									continue 2;

							$this->fields [$obj->getAssign ()] = $obj;
							$this->groups [$groupId][] = $obj->getAssign ();
						}
			}

		reset ($this->fields);
		reset ($this->groupsInfo);
		reset ($this->groups);
	}

	public function __call ($method, $arguments)
	{
		if ($method == 'goTo')
			return $this->goToAction (@$arguments [0]);
	}

	/**
	 * Singleton function.
	 *
	 * @return Database
	 * @static
	 * @see $forms
	 */
	static public function singleton ()
	{
		$files = func_get_args ();

		$class = __CLASS__;

		$action = Business::singleton ()->getAction (Action::TCURRENT);

		if ($action->getXmlPath () !== FALSE && trim ($action->getXmlPath ()) != '')
			array_unshift ($files, $action->getXmlPath ());

		array_push ($files, $action->getName (), $action->getEngine ());

		$form = new $class ($files);

		if (array_key_exists ($form->getAssign (), self::$forms))
			return self::$forms [$form->getAssign ()];

		self::$forms [$form->getAssign ()] =& $form;

		return $form;
	}

	/**
	 * Get XML section file name.
	 *
	 * @return string
	 */
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

	public function getAssign ()
	{
		return $this->assign;
	}

	public function setAssign ($assign)
	{
		$this->assign = $assign;
	}

	public function setLoad ($status = TRUE)
	{
		$this->loaded = $status;
	}

	public function isLoaded ()
	{
		return (bool) $this->loaded;
	}

	public function getId ()
	{
		return $this->itemId;
	}

	public function setId ($itemId)
	{
		$this->itemId = $itemId;
	}

	public function addField ($field, $group = 0)
	{
		if (!array_key_exists ($group, $this->groups))
			$group = 0;

		if (is_object ($field))
		{
			$this->fields [$field->getAssign ()] = $field;

			$this->groups [$group][] = $field->getAssign ();
		}
		elseif ($obj = Type::factory ($this->getTable (), $field))
		{
			while ($perm = $obj->getRestrict ())
				if (!$user->hasPermission ($perm))
					return FALSE;

			$this->fields [$obj->getAssign ()] = $obj;

			$this->groups [$group][] = $obj->getAssign ();

			return TRUE;
		}

		return FALSE;
	}

	public function removeField ()
	{
		$array = func_get_args ();

		foreach ($array as $trash => $field)
		{
			if (is_object ($field))
				$field = $field->getAssign ();

			if (!array_key_exists ($field, $this->fields))
				continue;

			unset ($this->fields [$field]);
		}

		return TRUE;
	}

	public function goToAction ($flag = FALSE)
	{
		global $section;

		if ($flag != 'fail' && isset ($_GET['goTo']) && array_key_exists ($_GET['goTo'], $this->go))
			$flag = $_GET['goTo'];
		elseif ($flag === FALSE || !array_key_exists ($flag, $this->go))
			return $section->getAction (Action::TDEFAULT);

		switch ($this->go [$flag])
		{
			case '[default]':
				return $section->getAction (Action::TDEFAULT);

			case '[same]':
				return $section->getAction (@$_GET ['toAction']);

			default:
				return $section->getAction ($this->go [$flag]);
		}
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
		if ($this->isLoaded ())
			return TRUE;

		if (is_array ($formData))
			foreach ($formData as $key => $value)
			{
				if (is_string ($value))
					$formData [$key] = $value;
			}
		else
			$formData = $_POST;

		foreach ($formData as $assign => $value)
		{
			if (!array_key_exists ($assign, $this->fields))
				continue;

			if ($this->fields [$assign]->isReadOnly ())
				$this->fields [$assign]->setValue (unserialize (base64_decode ($value)));
			else
				$this->fields [$assign]->setValue (self::fromForm ($this->fields [$assign], $value));
		}

		$this->setLoad ();

		return TRUE;
	}

	public function load ($sql, $ownOnly = FALSE)
	{
		if ($this->isLoaded ())
			return TRUE;

		if (is_numeric ($sql))
		{
			$itemId = $sql;

			$this->setId ($itemId);

			$fields = array ();
			foreach ($this->fields as $assign => $field)
				if ($field->isLoadable ())
					$fields [] = Database::toSql ($field);

			if (!sizeof ($fields))
			{
				reset ($this->fields);

				$this->setLoad ();

				return TRUE;
			}

			$sql = "SELECT ". implode (', ', $fields) ." FROM ". $this->getTable () ." WHERE ". $this->getPrimary () ." = '". $itemId ."'";

			if ($ownOnly)
				$sql .= " AND _user = '". User::singleton ()->getId () ."'";
		}

		//throw new Exception ($sql);

		$db = Database::singleton ();

		$sth = $db->prepare ($sql);

		$sth->execute ();

		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if (!$obj)
			return FALSE;

		foreach ($this->fields as $assign => $field)
			if ($field->isLoadable ())
				$this->fields [$assign] = Database::fromDb ($field, $obj);
			elseif (isset ($itemId))
				$this->fields [$assign]->load ($itemId);

		reset ($this->fields);

		$this->setLoad ();

		return TRUE;
	}

	public function save ($itemId = 0, $useLog = TRUE)
	{
		$fields = array ();
		$values = array ();
		$binds  = array ();

		foreach ($this->fields as $key => $field)
			if (!$field->isReadOnly () && $field->isSavable ())
			{
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

		// Legacy code. Old pattern to save last date of update still used by mandatory '_user' table.
		if ($useLog && array_pop (explode ('.', $this->getTable ())) == '_user')
		{
			$fields [] = '_update_date';
			$values [] = 'NOW()';
		}

		$mandatory = Database::getMandatoryColumns ($this->getTable ());

		foreach ($mandatory as $trash => $column)
		{
			$aux = $this->getFieldByColumn ($column);

			if (is_null ($aux) || !is_object ($aux) || !$aux->isSavable ())
				continue;

			$$column = $aux;
		}

		if (in_array ('_user', $mandatory) && !isset ($_user))
		{
			$fields [] = '_user';
			$values [] = User::singleton ()->getId ();
		}

		if (in_array ('_update', $mandatory) && !isset ($_update))
		{
			$fields [] = '_update';
			$values [] = 'NOW()';
		}

		if (in_array ('_change', $mandatory) && !isset ($_change))
		{
			$fields [] = '_change';
			$values [] = 'NOW()';
		}

		if (!is_numeric ($itemId) || (int) $itemId)
		{
			$aux = array ();
			foreach ($fields as $key => $field)
				$aux [] = $field ." = ". $values [$key];

			$sql = "UPDATE ". $this->getTable () ." SET ". implode (", ", $aux) ." WHERE ". $this->getPrimary () ." = '". $itemId ."'";
		}
		else
		{
			if (in_array ('_author', $mandatory) && !isset ($_author))
			{
				$fields [] = '_author';
				$values [] = User::singleton ()->getId ();
			}

			$itemId = Database::nextId ($this->getTable (), $this->getPrimary ());

			$sql = "INSERT INTO ". $this->getTable () ." (". $this->getPrimary () .", ". implode (", ", $fields) .") VALUES (". $itemId .", ". implode (", ", $values) .")";
		}

		// throw new Exception ($sql);

		$db = Database::singleton ();

		$sth = $db->prepare ($sql);

		foreach ($binds as $assign => $trash)
			if ($sizes [$assign] && $types [$assign] == PDO::PARAM_STR)
				$sth->bindParam ($values [$assign], $binds [$assign], $types [$assign], $sizes [$assign]);
			else
				$sth->bindParam ($values [$assign], $binds [$assign], $types [$assign]);

		if (!$sth->execute ())
			return FALSE;

		$this->setId ($itemId);

		Lucene::singleton ()->save ($itemId, $this->getResume ($itemId, TRUE));

		foreach ($this->fields as $key => $field)
			if (!$field->isSavable ())
				$field->save ($itemId);

		reset ($this->fields);

		return $itemId;
	}

	public function saveSession ($itemId = 0)
	{
		if (!isset ($_SESSION['_TITAN_FORMS_FOR_SAVE_']))
			return FALSE;

		$forms = $_SESSION['_TITAN_FORMS_FOR_SAVE_'];

		if (array_key_exists ('_ITEM_ID_', $forms) && $forms ['_ITEM_ID_'])
			$itemId = $forms ['_ITEM_ID_'];
		elseif ($itemId)
			$_SESSION['_TITAN_FORMS_FOR_SAVE_']['_ITEM_ID_'] = $itemId;
		else
			throw new Exception ('Deve existir uma tupla para este item no BD. Houve falha no salvamento dos dados.');

		if (!$this->save ($itemId))
			return FALSE;

		unset ($forms [$this->getAssign ()]);

		unset ($forms ['_ITEM_ID_']);

		foreach ($forms as $assign => $file)
		{
			$form = new Form ($file);

			$form->setAssign ($assign);

			$form->loadFromSession ();

			if (!$form->save ($itemId))
				throw new Exception ('Houve falha no salvamento de pelo menos um passo! Tente novamente.');

			unset ($_SESSION['_TITAN_FORMS_FOR_SAVE_'][$assign]);
		}

		return TRUE;
	}

	public function saveOnSession ()
	{
		$fields = array ();

		foreach ($this->fields as $key => $field)
			$fields [$field->getAssign ()] = $field->getValue ();

		reset ($this->fields);

		$_SESSION['_TITAN_FORMS_']['_TITAN_FORM_'. $this->getAssign () .'_'] = serialize ($fields);

		if (!array_key_exists ('_TITAN_FORMS_FOR_SAVE_', $_SESSION) || !array_key_exists ($this->getAssign (), $_SESSION['_TITAN_FORMS_FOR_SAVE_']))
			$_SESSION['_TITAN_FORMS_FOR_SAVE_'][$this->getAssign ()] = $this->getFile ();

		return TRUE;
	}

	public function loadFromSession ($itemId)
	{
		if ($this->isLoaded ())
			return TRUE;

		if (!isset ($_SESSION['_TITAN_FORMS_']['_TITAN_FORM_'. $this->getAssign () .'_']))
		{
			if (is_numeric ($itemId) && (int) $itemId)
			{
				$fields = array ();
				foreach ($this->fields as $assign => $field)
					if ($field->isLoadable ())
						$fields [] = Database::toSql ($field);

				if (!sizeof ($fields))
				{
					reset ($this->fields);

					$this->setLoad ();

					return TRUE;
				}

				$sql = "SELECT ". implode (', ', $fields) ." FROM ". $this->getTable () ." WHERE ". $this->getPrimary () ." = '". $itemId ."'";

				//throw new Exception ($sql);

				$db = Database::singleton ();

				$sth = $db->prepare ($sql);

				$sth->execute ();

				$obj = $sth->fetch (PDO::FETCH_OBJ);

				if (!$obj)
					return FALSE;

				foreach ($this->fields as $assign => $field)
					if ($field->isLoadable ())
						$this->fields [$assign] = Database::fromDb ($field, $obj);

				$_SESSION['_TITAN_FORMS_FOR_SAVE_']['_ITEM_ID_'] = $itemId;
			}

			$this->saveOnSession ();
		}
		else
		{
			$fields = unserialize ($_SESSION['_TITAN_FORMS_']['_TITAN_FORM_'. $this->getAssign () .'_']);

			foreach ($fields as $assign => $value)
				if (array_key_exists ($assign, $this->fields))
						$this->fields [$assign]->setValue ($value);
		}

		$this->setLoad ();

		return TRUE;
	}

	public function saveOnLdap ($ldap, $fields = FALSE, $uid = FALSE)
	{
		if (!is_array ($fields))
			$fields = array ();

		foreach ($this->fields as $assign => $field)
		{
			if (!$uid && $field->getLdap () == 'uid')
				$uid = Ldap::toLdap ($field);

			if ($field->isLdapField () && !$field->isReadOnly ())
				$fields [$field->getLdap ()] = Ldap::toLdap ($field);
		}

		if (!sizeof ($fields))
			return TRUE;

		if (!$ldap->isConnected ())
			$ldap->connect (FALSE, FALSE, TRUE);

		if (!$ldap->userExists ($uid))
		{
			$ldap->close ();

			throw new Exception ('O usuário não existe no servidor LDAP!');
		}

		$ldap->update ($fields, $uid);

		$ldap->close ();

		return TRUE;
	}

	public function createLdapUser ($uid, $ldap, $fields = FALSE)
	{
		if (!$ldap->isConnected ())
			$ldap->connect (FALSE, FALSE, TRUE);

		if (!is_array ($fields))
			$fields = array ();

		foreach ($this->fields as $assign => $field)
			if ($field->isLdapField ())
				$fields [$field->getLdap ()] = Ldap::toLdap ($field);

		if ($ldap->userExists ($uid))
		{
			$ldap->close ();

			throw new Exception ('O usuário já existe no servidor LDAP!');
		}

		$ldap->create ($fields, $uid);

		$ldap->close ();

		return TRUE;
	}

	public function deleteFromLdap ($ldap, $uid = FALSE)
	{
		if (!$ldap->isConnected ())
			$ldap->connect (FALSE, FALSE, TRUE);

		if (!$uid)
			foreach ($this->fields as $assign => $field)
				if ($field->isLdapField () && $field->getLdap () == 'uid')
				{
					$uid = Ldap::toLdap ($field);

					break;
				}

		if (!$ldap->userExists ($uid))
		{
			$ldap->close ();

			Message::singleton ()->addWarning ('O usuário não existe no servidor LDAP: '. $uid);

			return FALSE;
		}

		$ldap->delete ($uid);

		$ldap->close ();

		return TRUE;
	}

	public function loadFromLdap ($uid, $ldap)
	{
		if (!$ldap->isConnected ())
			$ldap->connect (FALSE, FALSE, FALSE);

		$search = array ();
		foreach ($this->fields as $assign => $field)
			if ($field->isLdapField ())
				$search [] = $field->getLdap ();

		$ldapUser = $ldap->load ($uid, $search);

		foreach ($this->fields as $assign => $field)
			if ($field->isLdapField () && array_key_exists (strtolower ($field->getLdap ()), $ldapUser))
				$this->fields [$assign] = Ldap::fromLdap ($field, $ldapUser [$field->getLdap ()]);

		$ldap->close ();

		return TRUE;
	}

	public function delete ($itemId = 0, $permanent = TRUE)
	{
		if (is_numeric ($itemId) && !(int) $itemId)
			return FALSE;

		if ($permanent)
			$sql = "DELETE FROM ". $this->getTable () ." WHERE ". $this->getPrimary () ." = '". $itemId ."'";
		else
			$sql = "UPDATE ". $this->getTable () ." SET _deleted = '1' WHERE ". $this->getPrimary () ." = '". $itemId ."'";

		$db = Database::singleton ();

		$sth = $db->prepare ($sql);

		if (!$sth->execute ())
			return FALSE;

		Lucene::singleton ()->delete ($itemId);

		return TRUE;
	}

	public function getResume ($itemId = 0, $friendly = FALSE)
	{
		if (!$this->isLoaded () && !$this->load ($itemId))
			return '[Impossível carregar informações do item #'. $itemId .']';

		$resume = "";

		if (!$friendly)
			$resume .= "[ID# ". $itemId ."] \n\n";

		while ($group = $this->getGroup ())
		{
			if ($group->getId ())
				$resume .= "> ". $group->getLabel () ."\n\n";

			while ($field = $this->getField (FALSE, $group->getId ()))
				$resume .= (trim ($field->getLabel ()) != '' ? $field->getLabel () .": \n" : '') . self::toText ($field) ." \n\n";
		}

		reset ($this->fields);

		return $resume;
	}

	public function getField ($assign = FALSE, $group = FALSE)
	{
		if ($assign !== FALSE)
		{
			if (!array_key_exists ($assign, $this->fields))
				return NULL;

			$field = $this->fields [$assign];

			if ($group !== FALSE)
				unset ($this->fields [$assign]);

			return $field;
		}

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

	public function getFieldByColumn ($column)
	{
		foreach ($this->fields as $assign => $field)
			if ($field->getColumn () == $column)
				return $field;

		return NULL;
	}

	public function getGroup ($id = FALSE)
	{
		if ($id !== FALSE)
		{
			if (!array_key_exists ($id, $this->groupsInfo))
				return NULL;

			return new Group ($this->groupsInfo [$id]);
		}

		$group = each ($this->groupsInfo);

		if ($group !== FALSE)
			return new Group ($group ['value']);

		reset ($this->groupsInfo);

		return NULL;
	}

	public static function toForm ($field, $scope = '')
	{
		if (!is_object ($field))
			return '<input type="text" class="field" name="'. $field .'" value="" />';

		$fieldName = $field->getAssign ();

		$fieldId = (trim ($scope) == '' ? 'field' : $scope) .'_'. $field->getAssign ();

		if ($field->isReadOnly ())
			return self::toHtml ($field) .' <input type="hidden" id="'. $fieldId .'" name="'. $fieldName .'" value="'. base64_encode (serialize ($field->getValue ())) .'" />';

		$instance = Instance::singleton ();

		$db = Database::singleton ();

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
			$file = $instance->getTypePath ($type) .'fromForm.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return $value;
	}

	public static function toLabel ($field, $showRequired = FALSE)
	{
		if (trim ($field->getLabel ()) == '')
			return '&nbsp;';

		if ($showRequired)
			return ($field->isRequired () ? '<label style="color: #990000;">*</label>' : '') . $field->getLabel ();

		return $field->getLabel ();
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
			$file = $instance->getTypePath ($type) .'toHtml.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return $field->getValue ();
	}

	public static function toText ($field)
	{
		if (!is_object ($field))
			return $field;

		$instance = Instance::singleton ();

		$fieldId = 'field_'. $field->getAssign ();

		$db = Database::singleton ();

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
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return strip_tags ($field->getValue ());
	}

	public static function toHelp ($field)
	{
		if (trim ($field->getHelp ()) == '')
			return '&nbsp;';

		return '<img src="'. Skin::singleton ()->getIconsFolder () .'help.gif" border="0" style="vertical-align: middle;" title="header=['. $field->getLabel () .'] body=['. $field->getHelp () .'] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />';
	}
}
?>
