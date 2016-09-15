<?php
class Backup
{
	static private $backup = FALSE;
	
	static private $lock = FALSE;
	
	private $path = FALSE;
	
	private $validity = 86400;
	
	private $timeout = 86400;
	
	private $folder = FALSE;
	
	private final function __construct ()
	{
		$array = Instance::singleton ()->getBackup ();
		
		if (!is_array ($array) || !array_key_exists ('path', $array) || trim ($array ['path']) == '')
			return;
		
		$this->path = $array ['path'];
		
		if (!file_exists ($this->path) && !@mkdir ($this->path, 0777))
			throw new Exception ('Impossible to create folder ['. $this->path .'].');
		
		if (array_key_exists ('validity', $array) && is_numeric ($array ['validity']) && (int) $array ['validity'])
			$this->validity = (int) $array ['validity'];
		
		if (array_key_exists ('timeout', $array) && is_numeric ($array ['timeout']) && (int) $array ['timeout'])
			$this->timeout = (int) $array ['timeout'];
		
		self::$lock = uniqid (rand (), TRUE);
	}
	
	static public function singleton ()
	{
		if (self::$backup !== FALSE)
			return self::$backup;
		
		$class = __CLASS__;
		
		self::$backup = new $class ();
		
		return self::$backup;
	}
	
	public function isActive ()
	{
		return self::$lock !== FALSE;
	}
	
	public function getPath ()
	{
		return $this->path;
	}
	
	public function getRealPath ()
	{
		return realpath ($this->path);
	}
	
	public function getLockPath ()
	{
		return $this->getRealPath () . DIRECTORY_SEPARATOR .'.lock';
	}
	
	public function getLockId ()
	{
		return self::$lock;
	}
	
	public function getValidity ()
	{
		return (string) $this->validity;
	}
	
	public function getTimeout ()
	{
		return (string) $this->timeout;
	}
	
	public function getLock ()
	{
		$handle = fopen ($this->getLockPath (), 'a+');
		
		if ($handle === FALSE)
			throw new Exception ('Impossible to read/create lock ['. $this->getLockPath () .'].');
		
		if (flock ($handle, LOCK_EX) && (filemtime ($this->getLockPath ()) < strtotime ('-'. $this->getTimeout () .' seconds') || !filesize ($this->getLockPath ())))
		{
			ftruncate ($handle, 0);
			
			rewind ($handle);
			
			fwrite ($handle, (string) self::$lock);
			
			flock ($handle, LOCK_UN);
			
			fclose ($handle);
			
			return TRUE;
		}
		
		fclose ($handle);
		
		return FALSE;
	}
	
	public function releaseLock ()
	{
		if (file_exists ($this->getLockPath ()) && self::$lock === trim (@file_get_contents ($this->getLockPath ())))
			return @unlink ($this->getLockPath ());
		
		return FALSE;
	}
	
	public function getFolder ()
	{
		if ($this->folder === FALSE)
		{
			do
			{
				$folder = 'B'. shortlyHash (md5 (uniqid (rand (), TRUE)));
			} while (file_exists ($this->getRealPath () . DIRECTORY_SEPARATOR . $folder));
			
			if (!@mkdir ($this->getRealPath () . DIRECTORY_SEPARATOR . $folder, 0777))
				throw new Exception ('Impossible to create unique backup folder ['. $this->getRealPath () . DIRECTORY_SEPARATOR . $folder .'].');
			
			$this->folder = $folder;
		}
		
		return $this->folder;
	}
	
	static public function clear ()
	{
		$folders = glob (self::singleton ()->getRealPath () . DIRECTORY_SEPARATOR .'B*');
		
		if (is_array ($folders))
			foreach ($folders as $trash => $folder)
				if (is_dir ($folder) && filemtime ($folder) < strtotime ('-'. self::singleton ()->getValidity () .' seconds'))
				{
					unlink ($folder . DIRECTORY_SEPARATOR .'.htaccess');
					unlink ($folder . DIRECTORY_SEPARATOR .'.htpasswd');
					
					removeDir ($folder);
					
					toLog ('Backup folder deleted ['. $folder .'].');
				}
		
		return TRUE;
	}
}
?>