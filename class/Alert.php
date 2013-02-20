<?
class Alert
{
	static private $alert = FALSE;
	
	private $templates = array ();
	
	private $tags = array ();
	
	static private $active = NULL;
	
	private final function __construct ()
	{
		$array = Instance::singleton ()->getAlert ();
		
		if (!array_key_exists ('xml-path', $array))
			throw new Exception ('Not located [xml-path] attribute on &lt;alert&gt;&lt;/alert&gt; tag in file [configure/titan.xml]!');
		
		$file = $array ['xml-path'];
		
		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';
		
		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);
			
			$array = $xml->getArray ();
			
			$array = $array ['alert-mapping'][0];
			
			xmlCache ($cacheFile, $array);
		}
		
		if (array_key_exists ('alert', $array))
			foreach ($array ['alert'] as $trash => $alert)
			{
				if (!array_key_exists ('id', $alert))
					continue;
				
				$this->templates [$alert ['id']] = $alert;
			}
	}
	
	static public function singleton ()
	{
		if (self::$alert !== FALSE)
			return self::$alert;
		
		$class = __CLASS__;
		
		self::$alert = new $class ();
		
		return self::$alert;
	}
	
	public function getAlerts ($userId)
	{
		$sql = "SELECT a.*, au._read, at._name AS author, av._name AS user,
				CASE WHEN a._until IS NULL THEN to_char (now(), 'MM-DD-YYYY') ELSE to_char (a._until, 'MM-DD-YYYY') END AS f_until, 
				extract (epoch from a._until) as u_until,
				to_char (a._create, 'HH24-MI-SS-MM-DD-YYYY') AS f_create
				FROM _alert_user au 
				INNER JOIN _alert a ON a._id = au._alert
				LEFT JOIN _user at ON at._id = a._user
				INNER JOIN _user av ON av._id = au._user
				WHERE au._user = :user AND au._delete = B'0' AND (a._until IS NULL OR a._until > CURRENT_TIMESTAMP)
				ORDER BY a._update DESC";
		
		$sth = Database::singleton ()->prepare ($sql);
		
		$sth->execute (array (':user' => $userId));
		
		$array = array ();
		
		$dTags = array ('[SYSTEM]' => Instance::singleton ()->getName (),
						'[URL]' => Instance::singleton ()->getUrl ());
		
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			if (!array_key_exists ($obj->_template, $this->templates))
				continue;
			
			$tags = $dTags;
			
			$tags ['[AUTHOR]'] = is_null ($obj->author) ? Instance::singleton ()->getName () : $obj->author;
			$tags ['[USER]'] = $obj->user;
			$tags ['[DAYS_MISSING]'] = (int) $obj->u_until <= 0 ? 0 : floor (($obj->u_until - time ()) / (60 * 60 * 24));
			
			$u = explode ('-', $obj->f_until);
			$tags ['[UNTIL]'] = strftime ('%x', mktime (0, 0, 0, (int) $u [0], (int) $u [1], (int) $u [2]));
			
			$d = explode ('-', $obj->f_create);
			$tags ['[DATE]'] = strftime ('%c', mktime ((int) $d [0], (int) $d [1], (int) $d [2], (int) $d [3], (int) $d [4], (int) $d [5]));
			
			$uTags = unserialize ($obj->_parameters);
			
			if (is_array ($uTags))
				$tags = array_merge ($tags, $uTags);
			
			$array [$obj->_id]['_MESSAGE_'] = str_replace (array_keys ($tags), $tags, $this->getFromTemplate ($obj->_template, 'message'));
			$array [$obj->_id]['_GO_'] = str_replace (array_keys ($tags), $tags, $this->getFromTemplate ($obj->_template, 'go'));
			$array [$obj->_id]['_ICON_'] = $this->getFromTemplate ($obj->_template, 'icon');
			$array [$obj->_id]['_READ_'] = (int) $obj->_read ? 'true' : 'false';
		}
		
		return $array;
	}
	
	private function getFromTemplate ($template, $attribute)
	{
		if (!array_key_exists ($template, $this->templates))
			return 'N/A';
		
		if (!array_key_exists ($attribute, $this->templates [$template]))
			return 'N/A';
		
		return $this->templates [$template][$attribute];
	}
	
	private function register ($template, $assign, $users, $tags = NULL, $until = NULL, $author = NULL, $overwrite = TRUE, $mail = NULL)
	{
		if ((!is_integer ($users) && !is_array ($users)) || (!is_null ($until) && !is_integer ($until)) || (!is_null ($until) && is_integer ($until) && $until > 0 && $until < time ()))
			return FALSE;
		
		if (!array_key_exists ($template, $this->templates))
			return FALSE;
		
		if (!is_null ($until) && !$until)
			$until = NULL;
		
		if (!is_array ($tags))
			$tags = array ();
		
		if (!is_array ($users))
			$users = array ($users);
		
		if (!sizeof ($users))
			return FALSE;
		
		if ((!is_array ($mail) && !is_integer ($mail)) || (!is_array ($mail) && is_integer ($mail) && !$mail))
			$mail = array ();
		
		if (is_integer ($mail) && $mail > time ())
			$mail = array ($mail);
		
		if (is_null ($author))
			$author = User::singleton ()->getId ();
		
		$db = Database::singleton ();
		
		$sql = "SELECT _id FROM _alert WHERE _template = :template AND _assign = :assign";
		
		$sth = $db->prepare ($sql);
		
		$sth->execute (array (':template' => $template, ':assign' => $assign));
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		try
		{
			if (is_object ($obj))
			{
				if (!$overwrite)
					return FALSE;
				
				$id = $obj->_id;
				
				$sql = "UPDATE _alert SET _until = timestamptz 'epoch' + :until * interval '1 second', _parameters = :parameters, _user = :user, _update = NOW() WHERE _id = :id";
				
				$db->prepare ($sql)->execute (array (':until' => $until, ':parameters' => serialize ($tags), ':user' => $author, ':id' => $id));
				
				$sql = "UPDATE _alert_user SET _read = B'0', _delete = B'0' WHERE _alert = :id";
				
				$sth = $db->prepare ($sql);
				
				$sth->execute (array (':id' => $id));
			}
			else
			{
				$id = Database::nextId ('_alert');
				
				$sql = "INSERT INTO _alert (_id, _template, _assign, _user, _until, _parameters) VALUES (:id, :template, :assign, :user, timestamptz 'epoch' + :until * interval '1 second', :parameters)";
				
				$db->prepare ($sql)->execute (array (':template' => $template, ':assign' => $assign, ':until' => $until, ':parameters' => serialize ($tags), ':user' => $author, ':id' => $id));
			}
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
			
			return FALSE;
		}
		
		$sql = "INSERT INTO _alert_user (_alert, _user) VALUES (:id, :user)";
		
		$sth = $db->prepare ($sql);
		
		foreach ($users as $trash => $user)
		{
			try
			{
				$sth->execute (array (':id' => $id, ':user' => $user));
			}
			catch (PDOException $e)
			{
				continue;
			}
		}
		
		try
		{
			$db->beginTransaction ();
			
			$db->exec ("DELETE FROM _alert_mail WHERE _alert = '". $id ."'");
			
			$sql = "INSERT INTO _alert_mail (_alert, _trigger) VALUES (:id, timestamptz 'epoch' + :trigger * interval '1 second')";
			
			$sth = $db->prepare ($sql);
			
			$now = time ();
			
			$today = mktime (0, 0, 0, date ('m'), date ('d') + 1, date ('Y'));
			
			foreach ($mail as $trash => $trigger)
				if ($trigger > $today)
					$sth->execute (array (':id' => $id, ':trigger' => $trigger));
			
			$sth->execute (array (':id' => $id, ':trigger' => $now));
			
			$db->commit ();
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
			
			$db->rollBack ();
		}
		
		$this->sendMail ($id);
		
		return TRUE;
	}
	
	public function sendMail ($id = NULL)
	{
		if (!is_null ($id) && (!is_numeric ($id) || !(int) $id))
			return FALSE;
		
		$today = mktime (0, 0, 0, date ('m'), date ('d') + 1, date ('Y'));
		
		$db = Database::singleton ();
		
		$sql = "SELECT a.*, at._name AS author, at._email AS a_mail, av._name AS user, av._email AS u_mail, av._id AS u_id, av._login AS u_login,
				to_char (a._until, 'DD/MM/YYYY') AS f_until, 
				extract (epoch FROM a._until) AS u_until,
				to_char (a._create, 'DD/MM/YYYY HH24:MI:SS') AS f_create
				FROM _alert_user au 
				INNER JOIN _alert a ON a._id = au._alert
				LEFT JOIN _user at ON at._id = a._user
				INNER JOIN _user av ON av._id = au._user
				WHERE (a._until IS NULL OR a._until > CURRENT_TIMESTAMP) AND ". (is_null ($id) ? "" : "au._alert = :id AND ") ."
				EXISTS (SELECT 1 FROM _alert_mail WHERE _alert = a._id AND _send = B'0' AND timestamptz 'epoch' + :today * interval '1 second' > _trigger)
				AND av._active = B'1' AND av._deleted = B'0'";
		
		$sth = $db->prepare ($sql);
		
		if (is_null ($id))
			$sth->execute (array (':today' => $today));
		else
			$sth->execute (array (':id' => $id, ':today' => $today));
		
		$dTags = array ('[SYSTEM]' => Instance::singleton ()->getName (),
						'[URL]' => Instance::singleton ()->getUrl ());
		
		$flag = FALSE;
		
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			if (!array_key_exists ($obj->_template, $this->templates) || 
				!array_key_exists ('subject', $this->templates [$obj->_template]) ||
				!array_key_exists (0, $this->templates [$obj->_template]) ||
				trim ($this->templates [$obj->_template]['subject']) == '' ||
				trim ($this->templates [$obj->_template][0]) == '')
				continue;
			
			try
			{
				$query = $db->query ("SELECT _alert FROM _user WHERE _id = '". $obj->u_id ."'");
				
				$enabled = $query->fetchColumn ();
				
				if (!is_null ($enabled) && !(int) $enabled)
					continue;
			}
			catch (PDOException $e)
			{}
			
			$auth = is_null ($obj->author) ? Instance::singleton ()->getName () : $obj->author;
			$mail = is_null ($obj->a_mail) ? Instance::singleton ()->getEmail () : $obj->a_mail;
			
			$tags = $dTags;
			
			$tags ['[AUTHOR]'] = $auth;
			$tags ['[USER]'] = $obj->user;
			$tags ['[DAYS_MISSING]'] = (int) $obj->u_until <= 0 ? 0 : floor (($obj->u_until - time ()) / (60 * 60 * 24));
			$tags ['[UNTIL]'] = $obj->f_until;
			$tags ['[DATE]'] = $obj->f_create;
			
			$hash = Security::singleton ()->getHash ();
			
			if (Instance::singleton ()->getFriendlyUrl ('disable-alerts') == '')
				$tags ['[DISABLE]'] = Instance::singleton ()->getUrl () .'titan.php?target=disableAlerts&login='. urlencode ($obj->u_login) .'&hash='. shortlyHash (md5 ($hash . $obj->user . $hash . $obj->u_id . $hash . $obj->u_mail . $hash));
			else
				$tags ['[DISABLE]'] = Instance::singleton ()->getUrl () . Instance::singleton ()->getFriendlyUrl ('disable-alerts') .'/'. urlencode ($obj->u_login) .'/'. shortlyHash (md5 ($hash . $obj->user . $hash . $obj->u_id . $hash . $obj->u_mail . $hash));
			
			$uTags = unserialize ($obj->_parameters);
			
			if (is_array ($uTags))
				$tags = array_merge ($tags, $uTags);
			
			if (!@mail ($obj->u_mail,
						str_replace (array_keys ($tags), $tags, $this->getFromTemplate ($obj->_template, 'subject')),
						str_replace (array_keys ($tags), $tags, $this->getFromTemplate ($obj->_template, 0)),
						"From: ". $auth ." <". $mail .">\r\nReply-To: ". $mail ."\r\nX-Mailer: PHP/". phpversion ()))
			{
				toLog ('Impossible to send alert mail! [To: '. $obj->u_mail .'] [Subject: '. str_replace (array_keys ($tags), $tags, $this->getFromTemplate ($obj->_template, 'subject')) .']');
				
				continue;
			}
			
			$flag = TRUE;
		}
		
		if ($flag)
		{
			try
			{
				$sql = "UPDATE _alert_mail SET _send = B'1' WHERE _send = B'0' AND timestamptz 'epoch' + :today * interval '1 second' > _trigger". (is_null ($id) ? "" : " AND _alert = :id");
				
				$sth = $db->prepare ($sql);
				
				if (is_null ($id))
					$sth->execute (array (':today' => $today));
				else
					$sth->execute (array (':id' => $id, ':today' => $today));
			}
			catch (PDOException $e)
			{
				toLog ($e->getMessage ());
				
				return FALSE;
			}
		}
		
		return $flag;
	}
	
	private function unregister ($template, $assign)
	{
		try
		{
			$sth = Database::singleton ()->prepare ("DELETE FROM _alert WHERE _template = :template AND _assign = :assign");
			
			$sth->bindParam (':template', $template, PDO::PARAM_STR, 64);
			$sth->bindParam (':assign', $assign, PDO::PARAM_STR, 64);
			
			$sth->execute ();
		}
		catch (PDOException $e)
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	public function read ($id, $user)
	{
		try
		{
			$sth = Database::singleton ()->prepare ("UPDATE _alert_user SET _read = B'1' WHERE _alert = :id AND _user = :user");
			
			$sth->bindParam (':id', $id, PDO::PARAM_INT);
			$sth->bindParam (':user', $user, PDO::PARAM_INT);
			
			$sth->execute ();
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
			
			return FALSE;
		}
		
		return TRUE;
	}
	
	public function delete ($id, $user)
	{
		try
		{
			$sth = Database::singleton ()->prepare ("UPDATE _alert_user SET _delete = B'1' WHERE _alert = :id AND _user = :user");
			
			$sth->bindParam (':id', $id, PDO::PARAM_INT);
			$sth->bindParam (':user', $user, PDO::PARAM_INT);
			
			$sth->execute ();
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
			
			return FALSE;
		}
		
		return TRUE;
	}
	
	public static function add ($template, $assign, $users, $tags = NULL, $until = NULL, $author = NULL, $overwrite = TRUE, $mail = NULL)
	{
		if (!self::isActive ())
			return FALSE;
		
		return Alert::singleton ()->register ($template, $assign, $users, $tags, $until, $author, $overwrite, $mail);
	}
	
	public static function remove ($template, $assign)
	{
		if (!self::isActive ())
			return FALSE;
		
		return Alert::singleton ()->unregister ($template, $assign);
	}
	
	public static function garbageCollector ()
	{
		$db = Database::singleton ();
		
		$sql = "SELECT a._id
				FROM _alert a
				WHERE 
					(NOT EXISTS (SELECT 1 FROM _alert_mail WHERE _alert = a._id AND _send = B'0') AND NOT EXISTS (SELECT 1 FROM _alert_user WHERE _alert = a._id AND _delete = B'0')) OR
					(a._until IS NOT NULL AND a._until < date_trunc ('day', now() - interval '1 day'))";
		
		$sth = $db->prepare ($sql);
		
		$sth->execute ();
		
		$garbage = $sth->fetchAll (PDO::FETCH_COLUMN, 0);
		
		try
		{
			if (sizeof ($garbage))
			{
				$sql = "DELETE FROM _alert WHERE _id IN (". implode (", ", $garbage) .")";
				
				$db->exec ($sql);
			}
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
		}
	}
	
	public static function isActive ()
	{
		if (is_null (self::$active))
			self::$active = Database::tableExists ('_alert');
		 
		 return self::$active;
	}
}
?>