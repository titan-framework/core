<?
/**
 * User.php
 *
 * Authenticate and realize logon of sytem user.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage security
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see Security, UserType, Group, AjaxLogon, AjaxPasswd, Ldap
 */
class User
{
	static private $user = FALSE;

	private $array = array ();

	private $hash = '';

	private $lastAccess	= 0;

	private $systems = array ();

	private $permissions = array ();

	private $chatRooms = array ();

	private $admin = FALSE;

	private $deleted = TRUE;

	private $active = FALSE;

	private $type = NULL;
	
	private $registered = array ();
	
	private $alerts = TRUE;

	private final function __construct ()
	{
		$this->array = array (	'id' 		 => NULL,
								'name' 		 => '',
								'login' 	 => '',
								'email'		 => '',
								'createDate' => '',
								'updateDate' => '',
								'lastLogon'	 => '');
	}

	static public function singleton ()
	{
		if (self::$user !== FALSE)
			return self::$user;

		$class = __CLASS__;

		if (isset ($_SESSION ['user']))
			self::$user =& $_SESSION ['user'];
		else
			self::$user = new $class ();

		return self::$user;
	}

	public function __set ($field, $value)
	{
		if (array_key_exists ($field, $this->array))
			$this->array [$field] = $value;
	}

	public function __get ($field)
	{
		if (array_key_exists ($field, $this->array))
			return $this->array [$field];

		return NULL;
	}

	public function authenticate ($login, $password)
	{
		$db = Database::singleton ();

		$validate = array ("'", '"', '\\', '--', '/*', '*/');
		$validLogin = str_replace ($validate, '', $login);
		$validPassword = str_replace ($validate, '', $password);

		if ($login !== $validLogin || $password !== $validPassword)
			throw new Exception (__ ('Incorrect User or Password!'));
		
		if (Security::singleton ()->encryptOnClient () && strlen ($password) != 40)
			throw new Exception (__ ('Incorrect User or Password!'));

		$sql = "SELECT *,
				to_char(_create_date, 'HH24-MI-SS-MM-DD-YYYY') AS _create_date,
				to_char(_update_date, 'HH24-MI-SS-MM-DD-YYYY') AS _update_date,
				to_char(_last_logon, 'HH24-MI-SS-MM-DD-YYYY') AS _last_logon
				FROM _user
				WHERE _login = '". $login ."'";

		$sth = $db->prepare ($sql);

		$sth->execute ();

		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if ($obj)
		{
			$type = Security::singleton ()->getUserType ($obj->_type);
			
			if (!is_object ($type))
				throw new Exception (__ ('User type not exists! Contact administrator.'));
	
			if ($type->useLdap ())
			{
				$ldap = $type->getLdap ();
	
				if (!$ldap->connect ($login, $password))
					throw new Exception (__ ('Incorrect User or Password!'));
	
				$ldap->close ();
			}
			elseif ((Security::singleton ()->encryptOnClient () && $password !== $obj->_password) || (!Security::singleton ()->encryptOnClient () && sha1 ($password) !== $obj->_password))
				throw new Exception (__ ('Incorrect User or Password!'));
		}
		else
		{
			while ($type = Security::singleton ()->getUserType ())
			{
				if (!$type->useLdap ())
					continue;
				
				$ldap = $type->getLdap ();
	
				if (!$ldap->connect ($login, $password))
				{
					$ldap->close ();
					
					continue;
				}
				
				$search = array ('displayname', 'cn', 'givenname', 'mail');
				
				$array = $ldap->load ($login);
				
				$ldap->close ();
				
				$nameValidate = array ('cn', 'displayname', 'givenname');
				
				$name = $login;
				
				foreach ($nameValidate as $trash => $value)
				{
					if (!array_key_exists ($value, $array) || trim ($array [$value]) == '')
						continue;
					
					$name = $array [$value];
					
					break;
				}
				
				$userId = Database::nextId ('_user', '_id');
				
				$fields = array ('_id' 	 	 => "'". $userId ."'",
								 '_login' 	 => "'". $login ."'",
								 '_name'	 => "'". $name ."'",
								 '_email'	 => "'". (array_key_exists ('mail', $array) ? trim ($array [$mail]) : '') ."'",
								 '_password' => "'". randomHash (13) .'_INVALID_HASH_'. randomHash (13) ."'",
								 '_active'	 => "B'1'",
								 '_deleted'	 => "B'0'",
								 '_type'	 => "'". $type->getName () ."'");
				
				$sql = "INSERT INTO _user (". implode (", ", array_keys ($fields)) .") VALUES (". implode (", ", $fields) .")";
				
				$sth = $db->prepare ($sql);
				
				$sth->execute ();
				
				try
				{
					$sql = "SELECT _group FROM _type_group WHERE _type = '". $type->getName () ."'";
				
					$sth = $db->prepare ($sql);
				
					$sth->execute ();
				
					$sthUser = $db->prepare ("INSERT INTO _user_group (_user, _group) VALUES ('". $userId ."', :group)");
				
					while ($obj = $sth->fetch (PDO::FETCH_OBJ))
						$sthUser->execute (array (':group' => $obj->_group));
				}
				catch (PDOException $e)
				{
					$message->addWarning (__('Unable to bind initial groups to the user. You should manually set the groups of the new user. [ [1] ]', $e->getMessage ()));
				}
				
				$sql = "SELECT *,
						to_char(_create_date, 'HH24-MI-SS-MM-DD-YYYY') AS _create_date,
						to_char(_update_date, 'HH24-MI-SS-MM-DD-YYYY') AS _update_date,
						to_char(_last_logon, 'HH24-MI-SS-MM-DD-YYYY') AS _last_logon
						FROM _user
						WHERE _login = '". $login ."'";
		
				$sth = $db->prepare ($sql);
		
				$sth->execute ();
		
				$obj = $sth->fetch (PDO::FETCH_OBJ);
				
				break;
			}
			
			if (!$obj)
				throw new Exception (__ ('Incorrect User or Password!'));
		}
		
		$this->id 	 	= $obj->_id;
		$this->name  	= $obj->_name;
		$this->login 	= $obj->_login;
		$this->email 	= $obj->_email;
		$this->active 	= (int) $obj->_active  ? TRUE : FALSE;
		$this->deleted 	= (int) $obj->_deleted ? TRUE : FALSE;
		$this->type		= $type;
		
		if (isset ($obj->_language))
			Localization::singleton ()->setLanguage ($obj->_language);
		
		if (isset ($obj->_alert))
			$this->alerts = (int) $obj->_alert ? TRUE : FALSE;
		
		if (isset ($obj->_timezone) && trim ($obj->_timezone) != '')
			Instance::singleton ()->setTimeZone ($obj->_timezone);
		
		$cd = explode ('-', $obj->_create_date);
		$ud = explode ('-', $obj->_update_date);
		$ll = explode ('-', $obj->_last_logon);
		
		$this->createDate  	= strftime ('%c', mktime ((int) $cd [0], (int) $cd [1], (int) $cd [2], (int) $cd [3], (int) $cd [4], (int) $cd [5]));
		$this->updateDate 	= strftime ('%c', mktime ((int) $ud [0], (int) $ud [1], (int) $ud [2], (int) $ud [3], (int) $ud [4], (int) $ud [5]));
		$this->lastLogon 	= strftime ('%c', mktime ((int) $ll [0], (int) $ll [1], (int) $ll [2], (int) $ll [3], (int) $ll [4], (int) $ll [5]));

		if (!$this->isActive ())
			throw new Exception (__ ('This user is inactive into the system!'));
		
		try
		{
			$db->beginTransaction ();
			
			$db->exec ("ALTER TABLE _user DISABLE TRIGGER USER");
			
			$db->exec ("UPDATE _user SET _last_logon = NOW() WHERE _id = '". $obj->_id ."'");
			
			$db->exec ("ALTER TABLE _user ENABLE TRIGGER USER");
			
			$db->commit ();
		}
		catch (PDOException $e)
		{
			$db->rollBack ();
			
			toLog ('Impossible to change information about logon time in _user table. ['. $e->getMessage () .']');
		}

		$this->lastAccess = time ();

		$this->hash	= sha1 ($this->login . Security::singleton ()->getHash () . $this->lastAccess);

		$this->setGroups ();

		$_SESSION ['user'] =& $this;

		return TRUE;
	}
	
	public function authenticateBySocialNetwork ($driver, $id, $idType = PDO::PARAM_INT)
	{
		if (!Social::singleton ()->socialNetworkExists ($driver))
			return FALSE;
		
		$db = Database::singleton ();
		
		$driver = Social::singleton ()->getSocialNetwork ($driver);
		
		$column = $driver->getIdColumn ();
		
		$sql = "SELECT *,
				to_char(_create_date, 'HH24-MI-SS-MM-DD-YYYY') AS _create_date,
				to_char(_update_date, 'HH24-MI-SS-MM-DD-YYYY') AS _update_date,
				to_char(_last_logon, 'HH24-MI-SS-MM-DD-YYYY') AS _last_logon
				FROM _user
				WHERE ". $column ." = :id";
		
		$sth = $db->prepare ($sql);
		
		$sth->bindParam (':id', $id, $idType);
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (!$obj)
			throw new Exception (__ ('Incorrect User or Password!'));
		
		$this->id 	 	= $obj->_id;
		$this->name  	= $obj->_name;
		$this->login 	= $obj->_login;
		$this->email 	= $obj->_email;
		$this->active 	= (int) $obj->_active  ? TRUE : FALSE;
		$this->deleted 	= (int) $obj->_deleted ? TRUE : FALSE;
		$this->type		= Security::singleton ()->getUserType ($obj->_type);
		
		if (isset ($obj->_language))
			Localization::singleton ()->setLanguage ($obj->_language);
		
		if (isset ($obj->_alert))
			$this->alerts = (int) $obj->_alert ? TRUE : FALSE;
		
		if (isset ($obj->_timezone) && trim ($obj->_timezone) != '')
			Instance::singleton ()->setTimeZone ($obj->_timezone);
		
		$cd = explode ('-', $obj->_create_date);
		$ud = explode ('-', $obj->_update_date);
		$ll = explode ('-', $obj->_last_logon);
		
		$this->createDate  	= strftime ('%c', mktime ((int) $cd [0], (int) $cd [1], (int) $cd [2], (int) $cd [3], (int) $cd [4], (int) $cd [5]));
		$this->updateDate 	= strftime ('%c', mktime ((int) $ud [0], (int) $ud [1], (int) $ud [2], (int) $ud [3], (int) $ud [4], (int) $ud [5]));
		$this->lastLogon 	= strftime ('%c', mktime ((int) $ll [0], (int) $ll [1], (int) $ll [2], (int) $ll [3], (int) $ll [4], (int) $ll [5]));

		if (!$this->isActive ())
			throw new Exception (__ ('This user is inactive into the system!'));
		
		try
		{
			$db->beginTransaction ();
			
			$db->exec ("ALTER TABLE _user DISABLE TRIGGER USER");
			
			$db->exec ("UPDATE _user SET _last_logon = NOW() WHERE _id = '". $obj->_id ."'");
			
			$db->exec ("ALTER TABLE _user ENABLE TRIGGER USER");
			
			$db->commit ();
		}
		catch (PDOException $e)
		{
			$db->rollBack ();
			
			toLog ('Impossible to change information about logon time in _user table. ['. $e->getMessage () .']');
		}

		$this->lastAccess = time ();

		$this->hash	= sha1 ($this->login . Security::singleton ()->getHash () . $this->lastAccess);

		$this->setGroups ();

		$_SESSION ['user'] =& $this;

		return TRUE;
	}

	public function update ()
	{
		$db = Database::singleton ();

		$sql = "SELECT *,
				to_char(_update_date, 'HH24-MI-SS-MM-DD-YYYY') AS _update_date
				FROM _user
				WHERE _id = '". $this->getId () ."'";

		$sth = $db->prepare ($sql);

		$sth->execute ();

		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if (!$obj)
			throw new Exception (__('Fail in the recover the updated data!'));

		$this->name  	= $obj->_name;
		$this->login 	= $obj->_login;
		$this->email 	= $obj->_email;
		
		if (isset ($obj->_language))
			Localization::singleton ()->setLanguage ($obj->_language);
		
		if (isset ($obj->_timezone) && trim ($obj->_timezone) != '')
			Instance::singleton ()->setTimeZone ($obj->_timezone);
		
		$ud = explode ('-', $obj->_update_date);
		
		$this->updateDate 	= strftime ('%c', mktime ((int) $ud [0], (int) $ud [1], (int) $ud [2], (int) $ud [3], (int) $ud [4], (int) $ud [5]));

		return TRUE;
	}

	public function isActive ()
	{
		return $this->active && !$this->deleted;
	}

	public function setGroups ()
	{
		$db = Database::singleton ();

		$this->permissions = array ();

		$sth = $db->prepare ("	SELECT _group.*, _permission._name AS _permission
								FROM _permission
								LEFT JOIN _group ON _group._id = _permission._group
								LEFT JOIN _user_group ON _user_group._group = _group._id
								WHERE _user_group._user = '". $this->id ."'");

		$sth->execute ();

		$this->chatRooms = array ();

		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			$this->permissions [$obj->_permission] = $obj->_permission;
			$this->systems [$obj->_id] = $obj->_name;

			if ((int) $obj->_admin) $this->admin = TRUE;

			if ((int) $obj->_chat)
				$this->chatRooms [] = $obj->_name;
		}

		if (!sizeof ($this->systems))
		{
			$sth = $db->prepare ("	SELECT _group.*
									FROM _user_group
									LEFT JOIN _group ON _user_group._group = _group._id
									WHERE _user_group._user = '". $this->id ."'");

			$sth->execute ();

			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			{
				$this->systems [$obj->_id] = $obj->_name;

				if ((int) $obj->_admin) $this->admin = TRUE;

				if ((int) $obj->_chat)
					$this->chatRooms [] = $obj->_name;
			}
		}

		return TRUE;
	}

	public function getGroups ()
	{
		return $this->systems;
	}

	public function getChatRooms ()
	{
		return $this->chatRooms ;
	}

	public function getType ()
	{
		return $this->type;
	}

	public function isLogged ()
	{
		$security = Security::singleton ();

		if (is_null ($this->getId ()) || 
			$this->hash != sha1 ($this->login . $security->getHash () . $this->lastAccess) ||
			($this->lastAccess + $security->getTimeout ()) < time ())
			return FALSE;
		
		$this->lastAccess = time ();

		$this->hash	= sha1 ($this->login . $security->getHash () . $this->lastAccess);

		return TRUE;
	}
	
	public function changeLanguage ($language)
	{
		try
		{
			Database::singleton ()->exec ("UPDATE _user SET _language = '". $language ."' WHERE _id = '". $this->getId () ."'");
		}
		catch (PDOException $e)
		{
			return FALSE;
		}
		
		return TRUE;
	}

	public function getName ()
	{
		return $this->name;
	}

	public function getLogin ()
	{
		return $this->login;
	}

	public function getId ()
	{
		return $this->id;
	}

	public function getEmail ()
	{
		return $this->email;
	}
	
	public function alertsEnabled ()
	{
		return $this->alerts;
	}

	public function getCreateDate ()
	{
		return $this->createDate;
	}

	public function getUpdateDate ()
	{
		return $this->updateDate;
	}

	public function getLastLogon ()
	{
		return $this->lastLogon;
	}

	public function isAdmin ()
	{
		return (bool) $this->admin;
	}
	
	public function addPermission ($permission, $forSection = FALSE)
	{
		if ($forSection === FALSE)
			$forSection = Business::singleton ()->getSection (Section::TCURRENT)->getName ();
		
		$permission = 'PERMISSION_'. $forSection .'_'. $permission;
		
		$this->permissions [$permission] = $permission;
		
		self::$user->permissions [$permission] = $permission;
		
		$_SESSION ['user']->permissions [$permission] = $permission;
	}

	public function hasPermission ($permission, $forSection = FALSE)
	{
		if ($forSection === FALSE)
			$forSection = Business::singleton ()->getSection (Section::TCURRENT)->getName ();

		return isset ($this->permissions ['PERMISSION_'. $forSection .'_'. $permission]);
	}

	public function accessSection ($section)
	{
		if (isset ($this->permissions ['ACCESS_SECTION_'. $section]))
			return TRUE;

		if ($this->isAdmin () && Business::singleton ()->sectionExists ($section) && Business::singleton ()->getSection ($section)->adminAccessible ())
			return TRUE;

		return FALSE;
	}

	public function accessAction ($action, $section)
	{
		if (isset ($this->permissions ['ACCESS_ACTION_'. $section .'_'. $action]))
			return TRUE;

		if ($this->isAdmin () && Business::singleton ()->getSection ($section)->adminAccessible ())
			return TRUE;

		return FALSE;
	}
	
	public function accessData ($id, $primary, $table, $column = '_user')
	{
		$sql = "SELECT COUNT(*) FROM ". $table ." WHERE ". $primary ." = '". $id ."' AND ". $column ." = '". $this->getId () ."'";
		
		$query = Database::singleton ()->query ($sql);
		
		if ((int) $query->fetchColumn ())
			return TRUE;
		
		return FALSE;
	}
	
	public function register ($table, $column, $primary, $value = NULL)
	{
		if (is_null ($value))
			$this->registered [$table][$column][$primary] = TRUE;
		elseif (!isset ($this->registered [$table][$column][$primary]) || is_array ($this->registered [$table][$column][$primary]))
			$this->registered [$table][$column][$primary][$value] = TRUE;
	}
	
	public function unregister ($table, $column, $primary, $value = NULL)
	{
		if (!isset ($this->registered [$table][$column][$primary]))
			return TRUE;
		elseif (is_null ($value) || !is_array ($this->registered [$table][$column][$primary]))
			unset ($this->registered [$table][$column][$primary]);
		else
			unset ($this->registered [$table][$column][$primary][$value]);
	}
	
	public function isRegistered ($table, $column, $primary, $value = NULL)
	{
		if (isset ($this->registered [$table][$column][$primary]) && !is_array ($this->registered [$table][$column][$primary]))
			return TRUE;
		
		if (is_null ($value))
			return isset ($this->registered [$table][$column][$primary]) && !is_array ($this->registered [$table][$column][$primary]);
		
		return isset ($this->registered [$table][$column][$primary][$value]);
	}
}
?>