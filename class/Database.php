<?php
/**
 * Database.php
 *
 * Database connection class. This class is used for instantiate a singleton
 * object for database connection.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage database
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see DatabaseMaker
 */
class Database
{
	/**
	 * This variable assure the existance of a unique Database object instance.
	 *
	 * @var Database
	 * @access private
	 * @static
	 */
	static private $database = FALSE;

	/**
	 * For PDO connection record.
	 *
	 * @var PDO
	 * @access private
	 */
	private $connection = FALSE;
	
	/**
	 * Schema of database.
	 *
	 * @var string
	 * @access private
	 */
	private $array = array ('sgbd'		=> '',
							'host'		=> 'localhost',
							'name'		=> '',
							'port'		=> '',
							'schema'	=> 'public',
							'user'		=> '',
							'password' 	=> '');

	/**
	 * Class constructor.
	 * Designed for singleton.
	 *
	 * @access private
	 * @final
	 * @see singleton ()
	 */
	private final function __construct ()
	{
		$db = Instance::singleton ()->getDatabase ();
		
		foreach ($this->array as $key => $value)
			if (array_key_exists ($key, $db))
				$this->array [$key] = (string) $db [$key];
		
		switch ($this->sgbd)
		{
			case 'MySQL':
				$dsn = 'mysql:host='. $this->host .';dbname='. $this->name;
				break;
			
			case 'SQLServer':
				$dsn = 'mssql:host='. $this->host .'; dbname='. $this->name;
				break;
			
			case 'FireBird':
				$dsn = 'firebird:User='. $this->user .';Password='. $this->password .';Database='. $this->name .';DataSource='. $this->host .';Port='. (!$this->port ? '3050' : $this->port);
				break;
			
			case 'Sybase':
				$dsn = 'sybase:host='. $this->host .'; dbname='. $this->name;
				break;
			
			case 'PostgreSQL':
				$dsn = 'pgsql:'. (!in_array ($this->host, array ('localhost', '127.0.0.1', '::1')) || $this->password != '' || PHP_OS != 'Linux' ? 'host='. $this->host .' port='. (trim ($this->port) == '' ? '5432' : $this->port) : '') .' dbname='. $this->name .' user='. $this->user .' password='. $this->password;
				break;
			
			case 'ODBC':
				$dsn = 'odbc:DSN=SAMPLE;UID='. $this->user .';PWD='. $this->password;
				break;
			
			case 'SQLite':
				$dsn = 'sqlite:'. $this->name;
				break;
			
			case 'OCI':
				$dsn = 'oci:dbname=//'. $this->host .':'. (trim ($this->port) == '' ? '1521' : $this->port) .'/'. $this->name;
				break;
			
			default:
				throw new Exception ('You need set SGBD database configuration in titan.xml!');
		}
		
		$dbh = new PDO ($dsn, $this->user, $this->password);
		
		$dbh->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$dbh->exec ("SET timezone TO '". Instance::singleton ()->getTimeZone () ."'");
		
		try
		{
			$lang = Localization::singleton ()->getLanguage ();
			
			$dbh->exec ("SET LC_MONETARY TO '". $lang ."'");
			$dbh->exec ("SET LC_NUMERIC  TO '". $lang ."'");
			$dbh->exec ("SET LC_TIME     TO '". $lang ."'");
			$dbh->exec ("SET LC_MESSAGES TO '". $lang ."'");
		}
		catch (PDOException $e)
		{}
		
		$dbh->exec ("SET datestyle TO ISO, DMY");
		
		if (trim ($this->schema) != '')
			$dbh->exec ('SET search_path = '. $this->schema);
		
		$this->connection = $dbh;
	}

	/**
	 * Singleton function.
	 * 
	 * @return Database
	 * @static
	 */
	static public function singleton ()
	{
		if (self::$database !== FALSE)
			return self::$database;
		
		$class = __CLASS__;
		
		self::$database = new $class ();
		
		return self::$database;
	}

	/**
	 * Magic method for call PDO methods by Database object.
	 *
	 * @param string $function
	 * @param array $args
	 * @return mixed
	 */
	public function __call ($function, $args)
	{
		return call_user_func_array (array (&$this->connection, $function), $args);
	}

	/**
	 * Verify connection.
	 *
	 * @return boolean
	 */
	public function isConnected ()
	{
		return $this->connection !== FALSE;
	}
	
	public function __get ($key)
	{
		if (array_key_exists ($key, $this->array))
			return $this->array [$key];
		
		return '';
	}
	
	public function __set ($key, $value)
	{
		if (array_key_exists ($key, $this->array))
			$this->array [$key] = $value;
	}

	/**
	 * Get default schema.
	 *
	 * @return string
	 */
	public function getSchema ()
	{
		return $this->schema;
	}
	
	public function getName ()
	{
		return $this->name;
	}
	
	public function getDbms ()
	{
		return $this->sgbd;
	}
	
	public function getHost ()
	{
		return $this->host;
	}
	
	public function getPort ()
	{
		return $this->port;
	}
	
	public function getUser ()
	{
		return $this->user;
	}

	/**
	 * Get the last serial inserted value in table.
	 *
	 * @static
	 * @param string $table
	 * @param string $primary
	 * @return integer
	 */
	public static function lastId ($table, $primary = NULL)
	{
		$db = self::singleton ();
		
		$array = explode ('.', $table);
		
		if (sizeof ($array) == 2)
		{
			$table = $array [1];
			$schema = $array [0];
		}
		else
		{
			$table = $array [0];
			$schema = $db->getSchema ();
		}
		
		if (is_string ($primary) && trim ($primary) != '')
			try
			{
				$db->exec ("BEGIN");
				
				$sth = $db->prepare ("SELECT pg_get_serial_sequence ('". $schema .".". $table ."', '". $primary ."') AS seq");
				
				$sth->execute ();
				
				$obj = $sth->fetch (PDO::FETCH_OBJ);
				
				if (!$obj || is_null ($obj->seq))
					throw new Exception ('Impossible to recovery sequence to column ['. $primary .'] on table ['. $schema .".". $table .'].');
				
				$sth = $db->prepare ("SELECT last_value FROM ". $obj->seq);
				
				$sth->execute ();
				
				$db->exec ("COMMIT");
				
				$result = $sth->fetch (PDO::FETCH_OBJ);
				
				if ($result)
					return $result->last_value;
				
				toLog ('Impossible to get last sequence value to ['. $obj->seq .'].');
			}
			catch (PDOException $e)
			{
				$db->exec ("ROLLBACK");
				
				toLog ($e->getMessage ());
			}
			catch (Exception $e)
			{
				$db->exec ("ROLLBACK");
				
				toLog ($e->getMessage ());
			}
		
		$sql = "SELECT a.adsrc AS seq
				FROM pg_class c
				JOIN pg_attrdef a ON c.oid = a.adrelid
				JOIN pg_namespace n ON c.relnamespace = n.oid
				WHERE c.relname = '". $table ."' AND n.nspname = '". $schema ."' AND a.adsrc ~ '^nextval'";
		
		$sth = $db->prepare ($sql);
		
		$sth->execute ();
		
		$result = FALSE;
		
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			if (is_null ($obj->seq) || is_numeric ($obj->seq) || !$obj->seq)
				continue;
			
			$sequence = str_replace (array ('::text', '::regclass', 'nextval(', ':', '(', ')', ' ', '"', '\''), '', trim ($obj->seq));
			
			$sequence = array_pop (explode ('.', $sequence));
			
			try
			{
				$db->exec ("BEGIN");
				
				$sthAux = $db->prepare ("SELECT last_value FROM ". $schema .".". $sequence);
				
				$sthAux->execute ();
				
				$db->exec ("COMMIT");
				
				$result = $sthAux->fetch (PDO::FETCH_OBJ);
				
				break;
			}
			catch (PDOException $e)
			{
				$db->exec ("ROLLBACK");
			}
		}
		
		if (!$result)
			return NULL;
		
		return $result->last_value;
	}

	/**
	 * Reserve and return de next serial value for table.
	 *
	 * @static
	 * @param string $table
	 * @param string $primary
	 * @return integer
	 */
	public static function nextId ($table, $primary = NULL)
	{
		$db = self::singleton ();
		
		$array = explode ('.', $table);
		
		if (sizeof ($array) == 2)
		{
			$table = $array [1];
			$schema = $array [0];
		}
		else
		{
			$table = $array [0];
			$schema = $db->getSchema ();
		}
		
		if (is_string ($primary) && trim ($primary) != '')
			try
			{
				$db->exec ("BEGIN");
				
				$sth = $db->prepare ("SELECT pg_get_serial_sequence ('". $schema .".". $table ."', '". $primary ."') AS seq");
				
				$sth->execute ();
				
				$obj = $sth->fetch (PDO::FETCH_OBJ);
				
				if (!$obj || is_null ($obj->seq))
					throw new Exception ('Impossible to recovery sequence to column ['. $primary .'] on table ['. $schema .".". $table .'].');
				
				$sth = $db->prepare ("SELECT nextval ('". $obj->seq ."')");
				
				$sth->execute ();
				
				$db->exec ("COMMIT");
				
				$result = $sth->fetch (PDO::FETCH_OBJ);
				
				if ($result)
					return $result->nextval;
				
				toLog ('Impossible to get next ID from sequence ['. $obj->seq .'].');
			}
			catch (PDOException $e)
			{
				$db->exec ("ROLLBACK");
				
				toLog ($e->getMessage ());
			}
			catch (Exception $e)
			{
				$db->exec ("ROLLBACK");
				
				toLog ($e->getMessage ());
			}
		
		$sql = "SELECT a.adsrc AS seq
				FROM pg_class c
				JOIN pg_attrdef a ON c.oid = a.adrelid
				JOIN pg_namespace n ON c.relnamespace = n.oid
				WHERE c.relname = '". $table ."' AND n.nspname = '". $schema ."' AND a.adsrc ~ '^nextval'";
		
		$sth = $db->prepare ($sql);
		
		$sth->execute ();
		
		$result = FALSE;
		
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			if (is_null ($obj->seq) || is_numeric ($obj->seq) || !$obj->seq)
				continue;
			
			$sequence = str_replace (array ('::text', '::regclass', 'nextval(', ':', '(', ')', ' ', '"', '\''), '', trim ($obj->seq));
			
			$sequence = array_pop (explode ('.', $sequence));
			
			try
			{
				$db->exec ("BEGIN");
				
				$sthAux = $db->prepare ("SELECT nextval ('". $schema .".". $sequence ."')");
				
				$sthAux->execute ();
				
				$db->exec ("COMMIT");
				
				$result = $sthAux->fetch (PDO::FETCH_OBJ);
				
				break;
			}
			catch (PDOException $e)
			{
				$db->exec ("ROLLBACK");
			}
		}
		
		if (!$result)
			return NULL;
		
		return $result->nextval;
	}
	
	public static function tableExists ($name)
	{
		$db = self::singleton ();
		
		$array = explode ('.', $name);
		
		if (sizeof ($array) == 2)
		{
			$schema = $array [0];
			$table = $array [1];
		}
		else
		{
			$schema = $db->getSchema ();
			$table = $array [0];
		}
		
		$sth = $db->prepare ("SELECT tablename FROM pg_tables WHERE schemaname = '". $schema ."' AND tablename = '". $table ."'");
		
		$sth->execute ();
		
		if ((int) $sth->rowCount ())
			return TRUE;
		
		return FALSE;
	}
	
	public static function getPrimaryColumn ($table)
	{
		$db = self::singleton ();
		
		$array = explode ('.', $table);
		
		if (sizeof ($array) == 2)
		{
			$schema = $array [0];
			$table = $array [1];
		}
		else
		{
			$schema = $db->getSchema ();
			$table = $array [0];
		}
		
		try
		{
			$sth = $db->query ("SELECT a.attname AS primary
								FROM pg_index i
								JOIN pg_class cr ON (cr.oid = i.indrelid)
								JOIN pg_namespace n ON (n.oid = cr.relnamespace)
								JOIN pg_attribute a ON (a.attrelid = cr.oid)
								JOIN pg_class ci ON (ci.oid = i.indexrelid)
								WHERE
									i.indisprimary AND
									n.nspname = '". $schema ."' AND cr.relname = '". $table ."' AND
									EXISTS (SELECT 1 FROM unnest(i.indkey) p(c) WHERE p.c = a.attnum)");
			
			return $sth->fetchAll (PDO::FETCH_COLUMN);
		}
		catch (PDOException $e)
		{}
		
		return array ();
	}
	
	public static function isUnique ($table, $column)
	{
		$db = self::singleton ();
		
		$array = explode ('.', $table);
		
		if (sizeof ($array) == 2)
		{
			$schema = $array [0];
			$table = $array [1];
		}
		else
		{
			$schema = $db->getSchema ();
			$table = $array [0];
		}
		
		try
		{
			$sth = $db->query ("SELECT COUNT(*) AS is_unique FROM (SELECT COUNT(". $column .") AS c FROM ". $schema .".". $table ." GROUP BY ". $column .") t WHERE t.c > 1");
			
			if ((int) $sth->fetchColumn ())
				return FALSE;
			
			return TRUE;
		}
		catch (PDOException $e)
		{}
		
		return FALSE;
	}
	
	public static function columnExists ($table, $column)
	{
		$db = self::singleton ();
		
		$array = explode ('.', $table);
		
		if (sizeof ($array) == 2)
		{
			$schema = $array [0];
			$table = $array [1];
		}
		else
		{
			$schema = $db->getSchema ();
			$table = $array [0];
		}
		
		try
		{
			$sth = $db->prepare ("SELECT 1 AS answer FROM information_schema.columns WHERE table_name = :table AND table_schema = :schema AND column_name = :column");
			
			$sth->bindParam (':table', $table, PDO::PARAM_STR);
			$sth->bindParam (':schema', $schema, PDO::PARAM_STR);
			$sth->bindParam (':column', $column, PDO::PARAM_STR);
			
			$sth->execute ();
			
			if ((int) $sth->fetchColumn ())
				return TRUE;
			
			return FALSE;
		}
		catch (PDOException $e)
		{}
		
		return FALSE;
	}
	
	public static function getMandatoryColumns ($table)
	{
		$db = self::singleton ();
		
		$array = explode ('.', $table);
		
		if (sizeof ($array) == 2)
		{
			$schema = $array [0];
			$table = $array [1];
		}
		else
		{
			$schema = $db->getSchema ();
			$table = $array [0];
		}
		
		$columns = array ('_user', '_create', '_update', '_author', '_devise', '_change');
		
		$sth = $db->prepare ("SELECT column_name FROM information_schema.columns WHERE table_name = :table AND table_schema = :schema AND column_name IN ('". implode ("', '", $columns) ."')");
		
		$sth->bindParam (':table', $table, PDO::PARAM_STR);
		$sth->bindParam (':schema', $schema, PDO::PARAM_STR);
		
		$sth->execute ();
		
		return $sth->fetchAll (PDO::FETCH_COLUMN, 0);
	}

	/**
	 * Returns formatted values for build SQL string.
	 *
	 * @static
	 * @param Type $field
	 * @return string
	 */
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

	/**
	 * Returns formatted values for build PDO bind SQL string.
	 *
	 * @static
	 * @param Type $field
	 * @return string
	 */
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

	/**
	 * Format DB value for load in class.
	 *
	 * @static
	 * @param Type $field
	 * @param anonymous object $field
	 * @return Type
	 */
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

	/**
	 * Get DB column name from field.
	 *
	 * @static
	 * @param Type $field
	 * @return string
	 */
	public static function toSql ($field)
	{
		if (!is_object ($field))
			return $field;
		
		$instance = Instance::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toSql.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		return $field->getTable () .'.'. $field->getColumn ();
	}

	/**
	 * Get DB column name from field for statement order use.
	 *
	 * @param Type $field
	 * @return string
	 */
	public static function toOrder ($field)
	{
		if (!is_object ($field))
			return $field;
		
		$instance = Instance::singleton ();
		
		$type = get_class ($field);
		
		do
		{
			$file = $instance->getTypePath ($type) .'toOrder.php';
			
			if (file_exists ($file))
				return include $file;
			
			$type = get_parent_class ($type);
			
		} while ($type != 'Type' && $type !== FALSE);
		
		return $field->getTable () .'.'. $field->getColumn ();
	}
	
	public static function size ()
	{
		$db = self::singleton ();
		
		$query = $db->query ("SELECT pg_database_size ('". $db->name ."') AS size");
		
		return (int) $query->fetchColumn ();
	}
}
?>