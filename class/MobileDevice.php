<?
class MobileDevice
{
	static private $active = NULL;
	
	const NO_USER = '_NO_USER_';
	const BY_LOGIN = '_BY_LOGIN_';
	const BY_MAIL = '_BY_MAIL_';
	const BY_ID = '_BY_ID_';
	
	public static function isActive ()
	{
		if (is_null (self::$active))
			self::$active = Database::tableExists ('_mobile');
		
		return self::$active;
	}
	
	public static function register ($name, $user = FALSE)
	{
		if (!self::isActive ())
			return NULL;
		
		$name = substr (str_replace (array ('"', "'"), '', trim ($name)), 0, 128);
		
		if ($user === FALSE)
			$user = User::singleton ()->getId ();
		
		$db = Database::singleton ();
		
		$id = Database::nextId ('_mobile', '_id');
		
		$pk = self::randomPrivateKey ();
		
		$sth = $db->prepare ("INSERT INTO _mobile (_id, _name, _pk, _user) VALUES (:id, :name, :pk, :user)");
		
		$sth->bindParam (':id', $id, PDO::PARAM_INT);
		$sth->bindParam (':name', $name, PDO::PARAM_STR, 128);
		$sth->bindParam (':pk', $pk, PDO::PARAM_STR, 16);
		$sth->bindParam (':user', $user, PDO::PARAM_INT);
		
		$sth->execute ();
		
		return (object) array ('name' => $name, 'id' => $id, 'pk' => $pk, 'user' => $user);
	}
	
	public static function unregister ($id, $user = FALSE)
	{
		if (!self::isActive ())
			return FALSE;
		
		if ($user === FALSE)
			$user = User::singleton ()->getId ();
		
		$sth = Database::singleton ()->prepare ("DELETE FROM _mobile WHERE _id = :id AND _user = :user");
		
		$sth->bindParam (':id', $id, PDO::PARAM_INT);
		$sth->bindParam (':user', $user, PDO::PARAM_INT);
		
		return $sth->execute ();
	}
	
	public static function randomPrivateKey ()
	{
		for ($s = '', $i = 0, $z = strlen ($a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') - 1; $i != 16; $x = rand (0, $z), $s .= $a{$x}, $i++);
		
		return $s;
	}
	
	public static function validatePrivateKey ($pk)
	{
		return substr ((string) preg_replace ('/[^0-9A-Z]/i', '', $pk), 0, 16); 
	}
	
	public static function formatPrivateKey ($pk)
	{
		$pk = self::validatePrivateKey ($pk);
		
		if (strlen ($pk) > 12)
			return substr ($pk,  0, 4) .'-'. substr ($pk,  4, 4) .'-'. substr ($pk,  8, 4) .'-'. substr ($pk,  12, 4);
		
		if (strlen ($pk) > 8)
			return substr ($pk,  0, 4) .'-'. substr ($pk,  4, 4) .'-'. substr ($pk,  8, 4);
		
		if (strlen ($pk) > 4)
			return substr ($pk,  0, 4) .'-'. substr ($pk,  4, 4);
		
		return $pk;
	}
	
	public static function getRegisteredDevice ($id)
	{
		$id = (int) preg_replace ('/[^0-9]/i', '', $id);
		
		if (!is_integer ($id) || !$id)
			throw new Exception ('Invalid value to parameter Client ID!');
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("SELECT _id AS id, _pk AS pk, _user AS user FROM _mobile WHERE _id = :id");
		
		$sth->bindParam (':id', $id, PDO::PARAM_INT);
		
		$sth->execute ();
		
		return $sth->fetch (PDO::FETCH_OBJ);
	}
}
