<?
abstract class SocialDriver
{
	protected $driver = NULL;
	
	protected $name = '';
	protected $authId = '';
	protected $authSecret = '';
	protected $userType = NULL;
	protected $autoRegister = TRUE;
	
	protected $path;
	
	protected $attributes = array ();
	
	protected $user = NULL;
	protected $id = NULL;
	
	protected function __construct ($array, $path)
	{
		$this->path = $path;
		
		$this->name = trim ($array ['driver']);
		$this->authId = trim ($array ['auth-id']);
		$this->authSecret = trim ($array ['auth-secret']);
		$this->userType = trim ($array ['register-as']);
		
		if (array_key_exists ('auto-register', $array))
			$this->autoRegister = strtoupper (trim ($array ['auto-register'])) == 'FALSE' ? FALSE : TRUE;
		
		if (array_key_exists ('attribute', $array))
			foreach ($array ['attribute'] as $trash => $att)
			{
				if (!array_key_exists ('name', $att) || trim ($att ['name']) == '' ||
					!array_key_exists ('column', $att) || trim ($att ['column']) == '')
					continue;
				
				$this->attributes [$att ['name']] = new SocialDriverAttribute ($att, $this->name);
			}
	}
	
	public function getName ()
	{
		return $this->name;
	}
	
	public function getPath ()
	{
		return $this->path;
	}
	
	protected function getUserType ()
	{
		return $this->userType;
	}
	
	public function getAttribute ($name = FALSE)
	{
		if ($name !== FALSE)
		{
			if (!array_key_exists ($name, $this->attributes))
				return NULL;
			
			return $this->attributes [$name];
		}
		
		$item = each ($this->attributes);
		
		if ($item === FALSE)
		{
			reset ($this->attributes);
			
			return NULL;
		}
		
		return $item ['value'];
	}
	
	public function getRequiredPermissions ()
	{
		$permissions = array ();
		
		while ($att = $this->getAttribute ())
			if ($att->getPermission () != '')
				$permissions [$att->getPermission ()] = 1;
		
		return array_keys ($permissions);
	}
	
	public function isAuthenticated ()
	{
		return !is_null ($this->user);
	}
	
	public function getUser ()
	{
		return $this->user;
	}
	
	public function isEnabled ()
	{
		$query = Database::singleton ()->query ("SELECT ". $this->getIdColumn () ." FROM _user WHERE _id = '". User::singleton ()->getId () ."'");
		
		return !is_null ($query->fetch (PDO::FETCH_COLUMN));
	}
	
	public function autoRegister ()
	{
		return $this->autoRegister;
	}
	
	public function setProfile ($profile)
	{
		$out = array ('id' => $profile ['id']);
		
		while ($att = $this->getAttribute ())
		{
			$out [$att->getName ()] = @$profile [$att->getName ()];
			
			$this->attributes [$att->getName ()]->setValue (@$profile [$att->getName ()]);
		}
		
		$this->profile = $out;
	}
	
	public function getProfile ()
	{
		return $this->profile;
	}
	
	abstract public function authenticate ();
	
	abstract public function register ($profile);
	
	abstract public function loadProfile ();
	
	abstract public function getLoginUrl ();
	
	abstract public function getConnectUrl ();
	
	abstract public function login ();
	
	abstract public function getIdColumn ();
	
	abstract public function getId ();
	
	abstract public function getUserUrl ();
}

class SocialDriverAttribute
{
	private $driver = '';
	
	private $name = '';
	private $column = '';
	private $adapter = '';
	private $permission = '';
	
	protected $value = NULL;
	
	public function __construct ($array, $driver)
	{
		$this->driver = $driver;
		
		$this->name = trim ($array ['name']);
		$this->column = trim ($array ['column']);
		$this->permission = trim (@$array ['permission']);
		
		if (array_key_exists ('adapter', $array) && trim ($array ['adapter']) != '')
			$adapter = $array ['adapter'];
		else
			$adapter = Instance::singleton ()->getReposPath () .'social/'. $this->driver .'/_adapter/'. $this->name .'.php';
		
		if (file_exists ($adapter))
			$this->adapter = $adapter;
	}
	
	public function getName ()
	{
		return $this->name;
	}
	
	public function getColumn ()
	{
		return $this->column;
	}
	
	public function getPermission ()
	{
		return $this->permission;
	}
	
	public function getAdapter ()
	{
		return $this->adapter;
	}
	
	public function setValue ($value)
	{
		if ($this->adapter == '')
			$this->value = $value;
		else
			$this->value = include $this->adapter;
	}
	
	public function getValue ()
	{
		return $this->value;
	}
}
?>