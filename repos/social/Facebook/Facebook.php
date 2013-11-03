<?
require dirname (__FILE__) . DIRECTORY_SEPARATOR .'_library'. DIRECTORY_SEPARATOR .'facebook.php';

class FacebookDriver extends SocialDriver
{
	protected $profile = NULL;
	
	public function __construct ($array, $path)
	{
		parent::__construct ($array, $path);
		
		$this->driver = new Facebook (array ('appId' => $this->authId, 'secret' => $this->authSecret));
	}
	
	public function getIdColumn ()
	{
		/*
		 * ALTER TABLE titan._user ADD COLUMN _facebook VARCHAR(50);
		 * ALTER TABLE titan._user ADD CONSTRAINT _user__facebook_key UNIQUE (_facebook);
		 */
		
		return '_facebook';
	}
	
	public function getId ()
	{
		$profile = $this->loadProfile ();
		
		if (isset ($profile ['username']) && trim ($profile ['username']) != '')
			return $profile ['username'];
		
		return NULL;
	}
	
	public function authenticate ()
	{
		$user = $this->driver->getUser ();
		
		if (!$user)
			return FALSE;
		
		/* 
		 * TODO: A verificacao de permissoes esta dando erro constante de access token - necessario arrumar e descomentar
		 *
		 
		try
		{
			$activePermissions = $this->driver->api ('/me/permissions');
		}
		catch (FacebookApiException $e)
		{
			toLog (print_r ($e, TRUE));
			
			return FALSE;
		}
		
		$requiredPermissions = $this->getRequiredPermissions ();
		
		if (count (array_intersect ($requiredPermissions, $activePermissions)) != count ($requiredPermissions))
			return FALSE;
		*/
		
		$this->user = $user;
		
		return TRUE;
	}
	
	public function loadProfile ($full = FALSE)
	{
		if (!$this->isAuthenticated ())
			return array ();
		
		if (is_array ($this->profile) && !$full)
			return $this->profile;
		
		try
		{
			$profile = $this->driver->api ('/me');
		}
		catch (FacebookApiException $e)
		{
			toLog (print_r ($e, TRUE));
			
			return array ();
		}
		
		if ($full)
			return $profile;
		
		$this->setProfile ($profile);
		
		return $this->getProfile ();
	}
	
	public function login ()
	{
		$profile = $this->loadProfile ();
		
		if (!array_key_exists ('email', $profile) || trim ($profile ['email']) == '' ||
			!array_key_exists ('name', $profile) || trim ($profile ['name']) == '' ||
			!array_key_exists ('username', $profile) || trim ($profile ['username']) == '')
			throw new Exception (__ ('Invalid data to search user (id, username, email or name)!'));
		
		try
		{
			User::singleton ()->authenticateBySocialNetwork ($this->getName (), $profile ['username']);
			
			return TRUE;
		}
		catch (Exception $e)
		{}
		
		$this->register ($profile);
		
		return User::singleton ()->authenticateBySocialNetwork ($this->getName (), $profile ['username']);
	}
	
	public function register ($profile)
	{
		$db = Database::singleton ();
		
		$sql = "SELECT _id FROM _user WHERE _facebook = :facebook";

		$sth = $db->prepare ($sql);
		
		$sth->bindParam (':facebook', $profile ['username'], PDO::PARAM_STR);
		
		$sth->execute ();

		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if ($obj)
			return $obj->_id;
		
		$sql = "SELECT _id, _type FROM _user WHERE _email = :email";

		$sth = $db->prepare ($sql);
		
		$sth->bindParam (':email', $profile ['email'], PDO::PARAM_STR);
		
		$sth->execute ();

		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if ($obj)
		{
			$type = Security::singleton ()->getUserType ($obj->_type);
			
			if (!is_object ($type))
				throw new Exception (__ ('User type not exists! Contact administrator.'));
			
			$sql = "UPDATE _user SET _facebook = :username WHERE _id = :id";
			
			$sth = $db->prepare ($sql);
			
			$sth->bindParam (':username', $profile ['username'], PDO::PARAM_STR);
			$sth->bindParam (':id', $obj->_id, PDO::PARAM_INT);
			
			$sth->execute ();
			
			return $obj->_id;
		}
		
		if (!$this->autoRegister ())
			throw new Exception (__ ('There is no user in the system linked to this social network profile!'));
		
		$_id = Database::nextId ('_user', '_id');
		
		while ($type = Security::singleton ()->getUserType ())
		{
			if (!$type->useLdap ())
				continue;
			
			$ldap = $type->getLdap ();
			
			if (!$ldap->connect (FALSE, FALSE, TRUE))
			{
				$ldap->close ();
				
				throw new Exception (__ ('This user type require LDAP integration! Please, contact administrator.'));
			}
			
			$search = $ldap->search (array ('uid'), '(mail='. $profile ['email'] .')');
			
			$ldap->close ();
			
			if (!(int) $search ['count'])
				continue;
			
			$_login = $search [0]['uid'][0];
			
			break;
		}
		
		if (!isset ($_login))
		{
			if (!Security::singleton ()->userTypeExists ($this->getUserType ()))
				throw new Exception (__ ('Invalid user type!'));
			
			$type = Security::singleton ()->getUserType ($this->getUserType ());
			
			if ($type->useLdap ())
			{
				$ldap = $type->getLdap ();
				
				if (!$ldap->connect (FALSE, FALSE, TRUE))
				{
					$ldap->close ();
					
					throw new Exception (__ ('This user type require LDAP integration! Please, contact administrator.'));
				}
				
				$_login = $this->getAttribute ('username')->getValue ();
				
				$count = 0;
				
				do
				{
					$query = $db->query ("SELECT COUNT(*) AS n FROM _user WHERE _login ILIKE '". $_login ."'");
					
					if ($count)
						$_login = $profile ['username'] . $count;
					
					$count++;
					
				} while ((int) $query->fetch (PDO::FETCH_COLUMN) || $ldap->userExists ($_login));
				
				$ldap->create ($ldap->getEssentialInput ($_login, $this->getAttribute ('name')->getValue (), $this->getAttribute ('email')->getValue (), randomHash (10), $_id), $_login);
				
				$ldap->close ();
			}
			else
			{
				$_login = $this->getAttribute ('username')->getValue ();
				
				$count = 0;
				
				do
				{
					$query = $db->query ("SELECT COUNT(*) AS n FROM _user WHERE _login ILIKE '". $_login ."'");
					
					if ($count)
						$_login = $profile ['username'] . $count;
					
					$count++;
					
				} while ((int) $query->fetch (PDO::FETCH_COLUMN));
			}
		}
		
		$fields = array ('_id' 	 	 => array ($_id, PDO::PARAM_INT),
						 '_login' 	 => array ($_login, PDO::PARAM_STR),
						 '_name'	 => array ($this->getAttribute ('name')->getValue (), PDO::PARAM_STR),
						 '_email'	 => array ($this->getAttribute ('email')->getValue (), PDO::PARAM_STR),
						 '_password' => array (randomHash (13) .'_INVALID_HASH_'. randomHash (13), PDO::PARAM_STR),
						 '_active'	 => array ('1', PDO::PARAM_STR),
						 '_deleted'	 => array ('0', PDO::PARAM_STR),
						 '_type'	 => array ($type->getName (), PDO::PARAM_STR),
						 '_facebook' => array ($profile ['username'], PDO::PARAM_STR));
		
		$alreadyAtts = array ('username', 'name', 'email');
		
		while ($att = $this->getAttribute ())
			if (!in_array ($att->getName (), $alreadyAtts))
				$fields [$att->getColumn ()] = array ($att->getValue ());
		
		try
		{
			$db->beginTransaction ();
			
			$sql = "INSERT INTO _user (". implode (", ", array_keys ($fields)) .") VALUES (:". implode (", :", array_keys ($fields)) .")";
			
			$sth = $db->prepare ($sql);
			
			foreach ($fields as $key => $array)
				if (sizeof ($array) > 1)
					$sth->bindParam (':'. $key, $array [0], $array [1]);
				else
					$sth->bindParam (':'. $key, $array [0]);
			
			$sth->execute ();
			
			$sql = "SELECT _group FROM _type_group WHERE _type = :type";
			
			$sth = $db->prepare ($sql);
			
			$sth->bindParam (':type', $type->getName (), PDO::PARAM_STR);
			
			$sth->execute ();
			
			$sthUser = $db->prepare ("INSERT INTO _user_group (_user, _group) VALUES (:id, :group)");
			
			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
				$sthUser->execute (array (':id' => $_id, ':group' => $obj->_group));
			
			$db->commit ();
			
			return $_id;
		}
		catch (PDOException $e)
		{
			$db->rollBack ();
			
			toLog ('Impossible to save user data in _user table. ['. $e->getMessage () .'] ['. print_r ($fields, TRUE) .']');
		}
		
		throw new Exception (__ ('Impossible to save your data! Please, contact administrator.'));
	}
	
	public function getLoginUrl ()
	{
		return $this->driver->getLoginUrl (array ('scope' => implode (', ', $this->getRequiredPermissions ())));
	}
	
	public function getConnectUrl ()
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT)->getName ();
		$action = Business::singleton ()->getAction (Action::TCURRENT)->getName ();
		
		return $this->driver->getLoginUrl (array ('scope' => implode (', ', $this->getRequiredPermissions ()), 'redirect_uri' => Instance::singleton ()->getUrl () .'titan.php?target=social&driver='. $this->getName () .'&section='. $section .'&action='. $action));
	}
	
	public function getUserUrl ($asLink = TRUE)
	{
		$query = Database::singleton ()->query ("SELECT ". $this->getIdColumn () ." FROM _user WHERE _id = '". User::singleton ()->getId () ."'");
		
		$id = $query->fetch (PDO::FETCH_COLUMN);
		
		if (trim ($id) == '')
			return '';
		
		$url = 'http://www.facebook.com/'. $id;
		
		if (!$asLink)
			return $url;
		
		return '<a href="'. $url .'" target="_blank">'. $url .'</a>';
	}
}
?>