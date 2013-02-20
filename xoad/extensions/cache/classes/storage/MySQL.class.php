<?php
/**
 * XOAD_Cache Storage MySQL Provider File.
 *
 * <p>This file defines the {@link XOAD_Cache_Storage_MySQL} Class.</p>
 * <p>You should not include this file directly. It is used
 * by {@link XOAD_Cache} extension.</p>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Cache
 *
 * @version		0.6.0.0
 *
 */

/**
 * Defines the table name in the MySQL database that is used to save
 * the cached data (default value: xoad_cache).
 *
 * @ignore
 *
 */
define('XOAD_CACHE_TABLE_NAME', 'xoad_cache');

/**
 * XOAD_Cache Storage MySQL Class.
 *
 * <p>This class is a {@link XOAD_Cache_Storage} successor.</p>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Cache
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Cache_Storage_MySQL extends XOAD_Cache_Storage
{
	/**
	 * Holds the MySQL server used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $server;

	/**
	 * Holds the MySQL user used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $user;

	/**
	 * Holds the MySQL password used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $password;

	/**
	 * Holds the MySQL database used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $database;

	/**
	 * Holds the MySQL port used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $port = 3306;

	/**
	 * Indicates whether to open a new connection to the MySQL server
	 * if an old one already exists.
	 *
	 * @access	protected
	 *
	 * @var		bool
	 *
	 */
	public $openNew;

	/**
	 * Creates a new instance of the {@link XOAD_Cache_Storage_MySQL} class.
	 *
	 * @access	public
	 *
	 * @param	string	$dsn	The data source name and parameters to use
	 *							when creating the instance.
	 *
 	 */
	public function __construct($dsn)
	{
		parent::__construct($dsn);
	}

	/**
	 * Creates a MySQL connection link.
	 *
	 * @access	private
	 *
	 * @return	resource
	 *
	 */
	public function &getConnection()
	{
		$server = $this->server;

		if ($this->port != 3306) {

			$server .= ':' . $this->port;
		}

		$connection = mysql_connect($server, $this->user, $this->password, $this->openNew);

		mysql_select_db($this->database, $connection);

		return $connection;
	}

	/**
	 * Closes a MySQL connection link.
	 *
	 * @access	private
	 *
	 * @return	void
	 *
	 */
	public function closeConnection(&$connection)
	{
		mysql_close($connection);

		$connection = null;
	}

	/**
	 * Escapes special characters in the {@link $unescapedString},
	 * taking into account the current charset of the connection.
	 *
	 * @access	private
	 *
	 * @return	string
	 *
	 */
	public function escapeString($unescapedString, $connection)
	{
		if (function_exists('mysql_real_escape_string')) {

			return mysql_real_escape_string($unescapedString, $connection);
		}

		return mysql_real_escape_string($unescapedString);
	}

	/**
 	 * Deletes old data from the cache.
	 *
	 * <p>This method is called before calling {@link load} to
	 * delete all expired data from the cache.</p>
	 *
	 * @access	public
	 *
	 * @return	bool	true on success, false otherwise.
	 *
	 */
	public function collectGarbage()
	{
		$connection =& $this->getConnection();

		$sqlQuery = '
			DELETE FROM
				`' . XOAD_CACHE_TABLE_NAME . '`
			WHERE
				`expire` < ' . time();

		mysql_query($sqlQuery, $connection);

		$this->closeConnection($connection);

		return true;
	}

	/**
 	 * Loads data from the cache with a given ID.
	 *
	 * @access	public
	 *
	 * @param	string	$id	The ID of the cached data.
	 *
	 * @return	mixed	The data in the cache with the given ID or null.
	 *
	 */
	public function load($id)
	{
		$connection =& $this->getConnection();

		$sqlQuery = '
			SELECT
				`data`
			FROM
				`' . XOAD_CACHE_TABLE_NAME . '`
			WHERE
				`id` = \'' . $this->escapeString($id, $connection) . '\'
				AND
				`expire` >= ' . time();

		$returnValue = null;

		$sqlResult = mysql_query($sqlQuery, $connection);

		while (($row = mysql_fetch_assoc($sqlResult)) !== false) {

			$returnValue = $row['data'];
		}

		mysql_free_result($sqlResult);

		$this->closeConnection($connection);

		return $returnValue;
	}

	/**
 	 * Saves data in the cache with a given ID and lifetime.
	 *
	 * @access	public
	 *
	 * @param	mixed	$id			The ID to use when saving the data.
	 *
	 * @param	int		$expires	The lifetime time in seconds for the
	 *								cached data.
	 *
	 * @param	mixed	$data		The data to cache.
	 *
	 * @return	bool	True on success, false otherwise.
	 *
	 */
	public function save($id, $expires, $data)
	{
		$connection =& $this->getConnection();

		if (empty($expires)) {

			$expires = XOAD_CACHE_LIFETIME;
		}

		$sqlQuery = '
			INSERT INTO
				`' . XOAD_CACHE_TABLE_NAME . '`
			(
				`id`,
				`expire`,
				`data`
			)
			VALUES
			(
				\'' . $this->escapeString($id, $connection) . '\',
				' . $this->escapeString(time() + $expires, $connection) . ',
				\'' . $this->escapeString($data, $connection) . '\'
			)';

		mysql_query($sqlQuery, $connection);

		$this->closeConnection($connection);

		return true;
	}
}
?>