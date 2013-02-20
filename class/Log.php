<?
class Log
{
	static private $log = FALSE;
	
	static private $connection = FALSE;
	
	private $path = FALSE;
	
	private $xml = FALSE;
	
	private $activities = array ();
	
	private $activityType = array ();
	
	private $loaded = FALSE;
	
	const EMERGENCY = '__PRIORITY_EMERGENCY__';
	const ALERT 	= '__PRIORITY_ALERT__';
	const CRITICAL 	= '__PRIORITY_CRITICAL__';
	const ERROR   	= '__PRIORITY_ERROR__';
	const SECURITY  = '__PRIORITY_SECURITY__';
	const WARNING  	= '__PRIORITY_WARNING__';
	const NOTICE 	= '__PRIORITY_NOTICE__';
	const INFO 		= '__PRIORITY_INFO__';
	const DEBUG		= '__PRIORITY_DEBUG__';
	
	private final function __construct ()
	{
		$array = Instance::singleton ()->getLog ();
		
		if (!array_key_exists ('db-path', $array))
			return;
		
		$this->path = $array ['db-path'];
		
		if (!file_exists ($this->getPath ()))
			if (!self::genLogDb ($this->getPath ()))
				return;
		
		try
		{
			$dbh = new PDO ('sqlite:'. $this->getPath ());
			
			$dbh->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
			
			return;
		}
		
		self::$connection = $dbh;
		
		if (array_key_exists ('xml-path', $array) && trim ($array ['xml-path']) != '')
			$this->xml = trim ($array ['xml-path']);
		
		$this->activities ['GENERIC'] = array ( '_LABEL_' 	=> '[_MESSAGE_]',
												'_CONTENT_'	=> '[_MESSAGE_]');
		
		$this->activityType ['GENERIC'] = 'Mensagens Gerais';
	}
	
	static public function singleton ()
	{
		if (self::$log !== FALSE)
			return self::$log;
		
		$class = __CLASS__;
		
		self::$log = new $class ();
		
		return self::$log;
	}
	
	public function getPath ()
	{
		return $this->path;
	}
	
	public function getDb ()
	{
		return self::$connection;
	}
	
	public function isActive ()
	{
		return self::$connection !== FALSE;
	}
	
	public function add ($activity, $message = FALSE, $priority = self::INFO, $logged = TRUE, $authoring = TRUE)
	{
		if (!$this->isActive ())
			return FALSE;
		
		if ($message === FALSE)
			$message = $activity;
		
		$array = array ();
		
		if ($authoring)
		{
			$user = User::singleton ();
			
			if ($type = $user->getType ())
			{
				$a = array ('user_id' 		=> array ($user->getId (), PDO::PARAM_INT),
							'user_name' 	=> array (substr ($user->getName (), 0, 256), PDO::PARAM_STR),
							'user_login' 	=> array (substr ($user->getLogin (), 0, 64), PDO::PARAM_STR),
							'user_email' 	=> array (substr ($user->getEmail (), 0, 512), PDO::PARAM_STR),
							'user_type' 	=> array (substr ($type->getName (), 0, 256), PDO::PARAM_STR));
				
				$array = array_merge ($array, $a);
			}
			elseif (!$logged)
				return FALSE;
		}
		
		if ($logged)
		{
			$action  = Business::singleton ()->getAction  (Action::TCURRENT);
			$section = Business::singleton ()->getSection (Section::TCURRENT);
			
			$b = array ('action_name' 	=> array ($action->getName (), PDO::PARAM_STR),
						'action_label' 	=> array ($action->getLabel (), PDO::PARAM_STR),
						'action_engine' => array ($action->getEngine (), PDO::PARAM_STR),
						'section_name' 	=> array ($section->getName (), PDO::PARAM_STR),
						'section_label' => array ($section->getLabel (), PDO::PARAM_STR),
						'section_component' => array ($section->getComponent (), PDO::PARAM_STR));
			
			$array = array_merge ($array, $b);
		}
		
		$c = array ('message' 	=> array ($message, PDO::PARAM_STR),
					'activity' 	=> array (substr ($activity, 0, 128), PDO::PARAM_STR),
					'priority' 	=> array ($priority, PDO::PARAM_STR),
					'ip' 		=> array ($_SERVER['REMOTE_ADDR'], PDO::PARAM_STR),
					'url' 		=> array ($_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'], PDO::PARAM_STR),
					'benchmark'	=> array ((int) time () - (int) $_SERVER['REQUEST_TIME'], PDO::PARAM_INT),
					'browser'	=> array ($_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR));
		
		$array = array_merge ($array, $c);
		
		$columns = array_keys ($array);
		
		$sql = "INSERT INTO log (". implode (", ", $columns) .") VALUES (:". implode (", :", $columns) .")";
		
		try
		{
			$sth = $this->getDb ()->prepare ($sql);
			
			foreach ($array as $column => $field)
				$sth->bindParam (':'. $column, $field [0], $field [1]);
			
			$sth->execute ();
		}
		catch (PDOException $e)
		{
			toLog ('Impossível gerar log de atividade: '. $e->getMessage ());
			
			return FALSE;
		}
		catch (Exception $e)
		{
			toLog ('Impossível gerar log de atividade: '. $e->getMessage ());
			
			return FALSE;
		}
		
		return TRUE;
	}
	
	static private function genLogDb ($path)
	{
		try
		{
			$dbh = new PDO ('sqlite:'. $path);
			
			$dbh->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			toLog ('Impossível gerar DB de Log em ['. $path .']: '. $e->getMessage ());
			
			return FALSE;
		}
		
		try
		{
			$sql = "CREATE TABLE log
					(
						id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
						user_id INTEGER,
						user_name VARCHAR(256),
						user_login VARCHAR(64),
						user_email VARCHAR(512),
						user_type VARCHAR(256),
						action_name VARCHAR(256),
						action_label VARCHAR(512),
						action_engine VARCHAR(256),
						section_name VARCHAR(256),
						section_label VARCHAR(512),
						section_component VARCHAR(256),
						message TEXT,
						activity VARCHAR(128),
						priority VARCHAR(32),
						date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
						ip VARCHAR(15),
						url VARCHAR(2048),
						browser VARCHAR(1024),
						benchmark INTEGER
					)";
			
			$dbh->exec ($sql);
		}
		catch (PDOException $e)
		{
			toLog ('Impossível gerar tabela [log] no DB de Log de Atividades em ['. $path .']: '. $e->getMessage ());
			
			return FALSE;
		}
		
		return TRUE;
	}
	
	public function getXmlPath ()
	{
		return $this->xml;
	}
	
	public function loadActivities ()
	{
		if ($this->loaded)
			return FALSE;
		
		$this->loaded = TRUE;
		
		$file = $this->getXmlPath ();
		
		if (!file_exists ($file))
			return FALSE;
		
		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';
		
		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);
			
			$array = $xml->getArray ();
			
			$array = $array ['log-mapping'][0];
			
			xmlCache ($cacheFile, $array);
		}
		
		if (!array_key_exists ('activity', $array))
			return FALSE;
		
		if (!is_array ($array ['activity']))
			$array ['activity'] = array ($array ['activity']);
		
		foreach ($array ['activity'] as $key => $activity)
		{
			if (!array_key_exists ('name', $activity))
				continue;
			
			if (array_key_exists (0, $activity))
				$text = $activity [0];
			else
				$text = '[_MESSAGE_]';
			
			if (array_key_exists ('message', $activity))
				$label = $activity ['message'];
			else
				$label = '[_MESSAGE_]';
			
			$this->activities [$activity ['name']] = array ('_LABEL_' => $label, '_CONTENT_' => $text);
			
			if (array_key_exists ('label', $activity))
				$this->activityType [$activity ['name']] = $activity ['label'];
		}
		
		reset ($this->activities);
		reset ($this->activityType);
		
		return TRUE;
	}
	
	public function getTypes ()
	{
		return $this->activityType;
	}
	
	public function getContent ($id)
	{
		try
		{
			$db = $this->getDb ();
			
			$sth = $db->prepare ("SELECT *, STRFTIME('%d-%m-%Y %H:%M:%S', date) AS fdate FROM log WHERE id = '". $id ."'");
			
			$sth->execute ();
			
			$obj = $sth->fetch (PDO::FETCH_OBJ);
		}
		catch (PDOException $e)
		{
			throw new Exception ('Impossível recuperar dados do item!');
		}
		
		if (array_key_exists ($obj->activity, $this->activities))
			$activity = $obj->activity;
		else
			$activity = 'GENERIC';
		
		return $this->parser ($this->activities [$activity]['_CONTENT_'], $obj);
	}
	
	public function getMessage ($id)
	{
		try
		{
			$db = $this->getDb ();
			
			$sth = $db->prepare ("SELECT *, STRFTIME('%d-%m-%Y %H:%M:%S', date) AS fdate FROM log WHERE id = '". $id ."'");
			
			$sth->execute ();
			
			$obj = $sth->fetch (PDO::FETCH_OBJ);
		}
		catch (PDOException $e)
		{
			throw new Exception ('Impossível recuperar dados do item!');
		}
		
		if (array_key_exists ($obj->activity, $this->activities))
			$activity = $obj->activity;
		else
			$activity = 'GENERIC';
		
		$parsed = $this->parser ($this->activities [$activity]['_LABEL_'], $obj);
		
		return String::limit ($parsed, 120);
	}
	
	private function parser ($text, $obj)
	{
		$array = array ('[_MESSAGE_]' 		=> $obj->message,
						'[_IP_]'			=> $obj->ip,
						'[_USER_NAME_]'		=> $obj->user_name,
						'[_USER_LOGIN_]'	=> $obj->user_login,
						'[_USER_MAIL_]'		=> $obj->user_email,
						'[_USER_TYPE_]'		=> !is_null ($obj->user_type) ? Security::singleton ()->getUserType ($obj->user_type)->getLabel () .' ('. $obj->user_type .')' : '--',
						'[_USER_ID_]'		=> $obj->user_id,
						'[_SECTION_]'		=> $obj->section_label,
						'[_COMPONENT_]'		=> $obj->section_component,
						'[_ACTION_]'		=> $obj->action_label,
						'[_ACTION_NAME_]'	=> $obj->action_name,
						'[_ENGINE_]'		=> $obj->action_engine,
						'[_BROWSER_]'		=> $obj->browser,
						'[_BENCHMARK_]'		=> $obj->benchmark,
						'[_DATE_]'			=> $obj->fdate);
		
		return str_replace (array_keys ($array), $array, $text);
	}
	
	public static function toValue ($field)
	{
		if (!is_object ($field))
			return $field;
		
		$instance = Instance::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toValue.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		return "'". $field->getValue () ."'";
	}
	
	public static function toBind ($field)
	{
		if (!is_object ($field))
			return $field;
		
		$instance = Instance::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toBind.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		return self::toValue ($field);
	}
	
	public static function fromDb ($field, $obj)
	{
		if (!is_object ($field) || !is_object ($obj))
			return NULL;
		
		$instance = Instance::singleton ();
		
		$fieldName = $field->getColumn ();
		
		$value = $obj->$fieldName;
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'fromDb.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		$field->setValue ($value);
		
		return $field;
	}
	
	public static function toSql ($field)
	{
		if (!is_object ($field))
			return $field;
		
		$instance = Instance::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toSql.SQLite.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		return Database::toSql ($field);
	}
	
	public static function toOrder ($field)
	{
		if (!is_object ($field))
			return $field;
		
		$instance = Instance::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toOrder.SQLite.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		return Database::toOrder ($field);
	}
}
?>