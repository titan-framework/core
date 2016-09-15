<?php
/**
 * Ldap.php
 *
 * Load LDAP definitions.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage security
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see Security, User, UserType, Group, AjaxLogon, AjaxPasswd
 */
class Ldap
{
	private $array = array ();
	
	private $ds = NULL;
	
	private $connected = FALSE;
	
	const SHA1 = 'SHA1';
	const MD5  = 'MD5';
	
	private $classes = array ();
	
	public function __construct ($input)
	{
		$this->array = array (	'id'		=> '',
								'host' 		=> '',
								'user'		=> '',
								'password'	=> '',
								'gid'		=> '',
								'dn'		=> '',
								'ou'		=> '',
								'update'	=> TRUE);
		
		foreach ($this->array as $key => $value)
			if (array_key_exists ($key, $input))
				if (is_bool ($value))
					$this->array [$key] = strtoupper ($input [$key]) == 'TRUE' ? TRUE : FALSE;
				else
					$this->array [$key] = $input [$key];
		
		if (array_key_exists ('password-hash', $input))
			$this->setPasswordHash ($input ['password-hash']);
		
		if (!array_key_exists ('class', $input) || !is_array ($input ['class']))
			$input ['class'] = array ();
		
		foreach ($input ['class'] as $trash => $class)
		{
			if (!array_key_exists ('name', $class) || trim ($class ['name']) == '')
				continue;
			
			$this->classes [trim ($class ['name'])] = $class;
		}
		
		$default = array (	'top' => array ('name' => 'top'),
							'person' => array ('name' => 'person'),
							'posixAccount' => array ('name' => 'posixAccount'), 
							'inetOrgPerson' => array ('name' => 'inetOrgPerson'));
		
		if (!array_key_exists ('top', $this->classes))
			$this->classes = array_merge ($default, $this->classes);
	}
	
	public function __get ($key)
	{
		if (!array_key_exists ($key, $this->array))
			return NULL;
		
		return $this->array [$key];
	}
	
	public function factory ($drive, $array)
	{
		if (array_key_exists ('path', $array) && trim ($array ['path']) != '' && is_dir ($array ['path']))
			$path = trim ($array ['path']);
		else
			$path = Instance::singleton ()->getReposPath () .'ldap/'. $drive .'/';
		
		if (!file_exists ($path . $drive .'.php'))
			return NULL;
		
		$class = 'Ldap'. ucfirst ($drive);
		
		if (!class_exists ($class, FALSE))
			require_once $path . $drive .'.php';
		
		if (!class_exists ($class, FALSE))
			return NULL;
		
		return new $class ($this, $path, $array);
	}
	
	public function getId ()
	{
		return $this->id;
	}
	
	public function getHost ()
	{
		return $this->host;
	}
	
	public function getGroupId ()
	{
		return $this->gid;
	}
	
	public function getOu ()
	{
		return $this->ou;
	}
	
	public function getDn ()
	{
		return $this->dn;
	}
	
	public function getDs ()
	{
		return $this->ds;
	}
	
	public function updateOnLogon ()
	{
		return $this->update;
	}
	
	public function isConnected ()
	{
		return (bool) $this->connected;
	}
	
	public function setPasswordHash ($pHash)
	{
		switch (strtoupper (trim ($pHash)))
		{
			case self::MD5:
			
				$this->passHash = self::MD5;
				
				break;
			
			case self::SHA1:
			default:
				
				$this->passHash = self::SHA1;
		}
	}
	
	public function encryptPassword ($passwd)
	{
		switch ($this->passHash)
		{
			case self::MD5:
				
				return '{MD5}'. base64_encode (pack ('H*', md5 ($passwd)));
			
			case self::SHA1:
			default:
				
				return '{SHA}'. base64_encode (pack ('H*', sha1 ($passwd)));
		}
	}
	
	public function getClasses ()
	{
		return array_keys ($this->classes);
	}
	
	public function getEssentialInput ($uid, $name, $email, $password, $id)
	{
		$input = array ('uid' => $uid, 'objectclass' => $this->getClasses ());
		
		foreach ($this->classes as $drive => $class)
		{
			if (!is_object ($class))
				$this->classes [$drive] = $this->factory ($drive, $class);
			
			$input = array_merge ($input, $this->classes [$drive]->genRequiredFields ($uid, $name, $email, $password, $id));
		}
		
		return $input;
	}
	
	public function getEssentialPassword ($uid, $password)
	{
		$input = array ();
		
		foreach ($this->classes as $drive => $class)
		{
			if (!is_object ($class))
				$this->classes [$drive] = $this->factory ($drive, $class);
			
			$input = array_merge ($input, $this->classes [$drive]->genPasswordFields ($uid, $password));
		}
		
		return $input;
	}
	
	public function connect ($uid = FALSE, $passwd = FALSE, $asAdmin = FALSE)
	{
		$this->ds = ldap_connect ($this->host);
		
		if (!$this->ds)
			throw new Exception (__ ('Impossible to connect on LDAP server! [[1]]', ldap_error ($this->ds)));
		
		if (!ldap_set_option ($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3))
			throw new Exception (__ ('Impossble to enable TLS for LDAP connection! [[1]]', ldap_error ($this->ds)));
		
		set_error_handler ('logPhpError');
		
		if ($asAdmin)
			$bind = ldap_bind ($this->ds, 'cn='. $this->user .','. $this->dn, $this->password);
		elseif ($uid === FALSE || $passwd === FALSE)
			$bind = ldap_bind ($this->ds);
		else
			$bind = ldap_bind ($this->ds, 'uid='. $uid .',ou='. $this->ou .','. $this->dn, $passwd);
		
		restore_error_handler ();
		
		if (!$bind)
			return FALSE;
		
		$this->connected = TRUE;
		
		return TRUE;
	}
	
	public function create ($input, $uid = FALSE)
	{
		if ($uid === FALSE)
			if (!array_key_exists ('uid', $input))
				return FALSE;
			else
				$uid = $input ['uid'];
		
		set_error_handler ('logPhpError');
		
		$flag = ldap_add ($this->ds, 'uid='. $uid .',ou='. $this->ou .','. $this->dn, $input);
		
		restore_error_handler ();
		
		if (!$flag)
			throw new Exception (__ ('Impossble to create LDAP entry! [[1]]', ldap_error ($this->ds)));
		
		return TRUE;
	}
	
	public function delete ($uid)
	{
		set_error_handler ('logPhpError');
		
		$flag = ldap_delete ($this->ds, 'uid='. $uid .',ou='. $this->ou .','. $this->dn);
		
		restore_error_handler ();
		
		if (!$flag)
			throw new Exception (__ ('Impossible to delete LDAP entry! [[1]]', ldap_error ($this->ds)));
		
		return TRUE;
	}
	
	public function load ($uid, $fields = FALSE)
	{
		if (!is_array ($fields))
			$fields = array ();
		
		$filter = '(&(uid='. $uid .'))';
		
		$info = $this->search ($fields, $filter);
		
		if (!(int) $info ['count'])
			throw new Exception (__ ('User not found in LDAP server! [[1]]', $uid));
		
		$result = array ();
		foreach ($info [0] as $key => $array)
			if (is_array ($array))
				$result [$key] = $array [0];
		
		return $result;
	}
	
	public function update ($input, $uid = FALSE)
	{
		if ($uid === FALSE)
			if (!array_key_exists ('uid', $input))
				return FALSE;
			else
				$uid = $input ['uid'];
		
		set_error_handler ('logPhpError');
		
		$flag = ldap_modify ($this->ds, 'uid='. $uid .',ou='. $this->ou .','. $this->dn, $input);
		
		restore_error_handler ();
		
		if (!$flag)
			throw new Exception (__ ('Impossible to edit LDAP entry! [[1]]', ldap_error ($this->ds)));
		
		return TRUE;
	}
	
	public function move ($uid, $new)
	{
		if (!is_object ($new) || get_class ($new) != __CLASS__)
			throw new Exception (__ ('Impossble to move LDAP entry! [[1]]', print_r ($new, TRUE)));
		
		set_error_handler ('logPhpError');
		
		$flag = ldap_rename ($this->ds, 'uid='. $uid .',ou='. $this->ou .','. $this->dn, 'uid='. $uid .',ou='. $new->getOu () .','. $new->getDn (), TRUE);
		
		restore_error_handler ();
		
		if (!$flag)
			throw new Exception (__ ('Impossble to move LDAP entry! [[1]]', ldap_error ($this->ds)));
		
		return TRUE;
	}
	
	public function search ($fields, $filter)
	{
		set_error_handler ('logPhpError');
		
		$search = ldap_search ($this->ds, 'ou='. $this->ou .','. $this->dn, $filter, $fields);
		
		/*
		if($orderParam)
			ldap_sort($this->ds, $sr, $orderParam);
		
   		$this->rows = ldap_count_entries($this->ds,$search);
		*/
		
		$info = ldap_get_entries ($this->ds, $search);
		
		restore_error_handler ();
		
		return $info;
	}
	
	public function userExists ($uid)
	{
		$info = $this->search (array (), '(&(uid='. $uid .'))');
		
		return (int) $info ['count'];
	}
	
	public function close ()
	{
		@ldap_close ($this->ds);
		
		$this->connected = FALSE;
	}
	
	public static function toLdap ($field)
	{
		if (!is_object ($field))
			return $field;
		
		$instance = Instance::singleton ();
		
		$db = Database::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toLdap.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		return removeAccents (Form::toText ($field));
	}
	
	public static function fromLdap ($field, $value)
	{
		if (!is_object ($field))
			return NULL;
		
		$instance = Instance::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'fromLdap.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		if (method_exists ($field, 'getMaxLength') && (int) $field->getMaxLength ())
			$value = substr ($value, 0, $field->getMaxLength ());
		
		$field->setValue ($value);
		
		return $field;
	}
}
?>