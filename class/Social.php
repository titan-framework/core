<?
class Social
{
	static private $social = FALSE;
	
	static private $active = NULL;
	
	private $networks = array ();
	
	private final function __construct ()
	{
		$array = Instance::singleton ()->getSocial ();
		
		if (!array_key_exists ('xml-path', $array))
			throw new Exception ('Not located [xml-path] attribute on &lt;social&gt;&lt;/social&gt; tag in file [configure/titan.xml]!');
		
		$file = $array ['xml-path'];
		
		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';
		
		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);
			
			$array = $xml->getArray ();
			
			$array = $array ['social-mapping'][0];
			
			xmlCache ($cacheFile, $array);
		}
		
		if (array_key_exists ('social', $array))
			foreach ($array ['social'] as $trash => $social)
			{
				if (!array_key_exists ('driver', $social) || trim ($social ['driver']) == '' ||
					!array_key_exists ('register-as', $social) || trim ($social ['register-as']) == '' ||
					!array_key_exists ('auth-id', $social) || trim ($social ['auth-id']) == '' ||
					!array_key_exists ('auth-secret', $social) || trim ($social ['auth-secret']) == '')
					continue;
				
				if (!Security::singleton ()->userTypeExists ($social ['register-as']))
					continue;
				
				$driver = trim ($social ['driver']);
				
				if (array_key_exists ('path', $social) && trim ($social ['path']) != '')
					$path = trim ($social ['path']);
				else
					$path = Instance::singleton ()->getReposPath () .'social/'. $driver .'/';
				
				if (!is_dir ($path))
					continue;
				
				require $path . $driver .'.php';
				
				$class = $driver .'Driver';
				
				$this->networks [$social ['driver']] = new $class ($social, $path);
			}
		
		reset ($this->networks);
	}
	
	static public function singleton ()
	{
		if (self::$social !== FALSE)
			return self::$social;
		
		$class = __CLASS__;
		
		self::$social = new $class ();
		
		return self::$social;
	}
	
	public function getSocialNetwork ($driver = FALSE)
	{
		if ($driver !== FALSE)
		{
			if (!array_key_exists ($driver, $this->networks))
				return NULL;
			
			return $this->networks [$driver];
		}
		
		$item = each ($this->networks);
		
		if ($item === FALSE)
		{
			reset ($this->networks);
			
			return NULL;
		}

		return $item ['value'];
	}
	
	public function socialNetworkExists ($driver)
	{
		return array_key_exists ($driver, $this->networks);
	}
	
	public static function isActive ()
	{
		if (is_null (self::$active))
			self::$active = sizeof (Instance::singleton ()->getSocial ()) && Database::isUnique ('_user', '_email');
		
		return self::$active;
	}
}