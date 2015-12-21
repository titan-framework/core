<?
class BrowserDevice
{
	static private $active = NULL;
	
	public static function isActive ()
	{
		if (is_null (self::$active))
			self::$active = Database::tableExists ('_browser');
		
		return self::$active;
	}
	
	public static function register ($user = FALSE, $validity = 3600)
	{
		if (!self::isActive ())
			return NULL;
		
		if ($user === FALSE)
			$user = User::singleton ()->getId ();
		
		$db = Database::singleton ();
		
		$pk = self::randomPrivateKey ();
		
		$validity += time ();
		
		try
		{
			$sth = $db->prepare ("INSERT INTO _browser (_pk, _user, _validity) VALUES (:pk, :user, :validity)");
			
			$sth->bindParam (':pk', $pk, PDO::PARAM_STR, 256);
			$sth->bindParam (':user', $user, PDO::PARAM_INT);
			$sth->bindParam (':validity', $validity, PDO::PARAM_INT);
			
			$sth->execute ();
		}
		catch (PDOException $e)
		{
			$sth = $db->prepare ("UPDATE _browser SET _pk = :pk, _validity = :validity, _update = now(), _access = now(), _counter = _counter + 1 WHERE _user = :user");
			
			$sth->bindParam (':pk', $pk, PDO::PARAM_STR, 256);
			$sth->bindParam (':user', $user, PDO::PARAM_INT);
			$sth->bindParam (':validity', $validity, PDO::PARAM_INT);
			
			$sth->execute ();
		}
		
		return (object) array ('user' => $user, 'pk' => $pk);
	}
	
	public static function randomPrivateKey ()
	{
		for ($s = '', $i = 0, $z = strlen ($a = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') - 1; $i != 256; $x = rand (0, $z), $s .= $a{$x}, $i++);
		
		return $s;
	}
	
	public static function validatePrivateKey ($pk)
	{
		return substr ((string) preg_replace ('/[^0-9A-Za-z]/i', '', $pk), 0, 256); 
	}
	
	public static function getKeyForRegisteredUser ($user = FALSE)
	{
		if (!self::isActive ())
			return NULL;
		
		if ($user === FALSE)
			$user = User::singleton ()->getId ();
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("SELECT _pk FROM _browser WHERE _user = :user AND _validity > :time LIMIT 1");
		
		$time = time ();
		
		$sth->bindParam (':user', $user, PDO::PARAM_INT);
		$sth->bindParam (':time', $time, PDO::PARAM_INT);
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (is_null ($obj))
			throw new ApiException (__ ('Invalid or old user credentials!'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);
		
		return $obj->_pk;
	}
	
	public static function registerAccess ($user = FALSE)
	{
		if (!self::isActive ())
			return NULL;
		
		if ($user === FALSE)
			$user = User::singleton ()->getId ();
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("UPDATE _browser SET _access = now(), _counter = _counter + 1 WHERE _user = :user");
		
		$sth->bindParam (':user', $user, PDO::PARAM_INT);
		
		return $sth->execute ();
	}
}