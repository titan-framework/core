<?php
/**
 * XOAD Events PearDB Provider file.
 *
 * <p>This file defines the {@link XOAD_Events_Storage_PearDB} Class.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 * require_once('DB.php'); // Include the Pear::DB package
 *
 * require_once(XOAD_BASE . '/classes/events/storage/PearDB.class.php');
 *
 * $storage = new XOAD_Events_Storage_PearDB('type=?;server=?;user=?;password=?;database=?;[port=?]');
 *
 * $storage->postEvent('event', 'class');
 *
 * ?>
 * </code>
 *
 * @author		Norm 2782
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Events
 *
 * @version		0.6.0.0
 *
 */

/**
 * Defines the table name in the PearDB database that is used to save the
 * events information (default value: xoad_events).
 *
 * @ignore
 *
 */
define('XOAD_EVENTS_TABLE_NAME', 'xoad_events');

/**
 * XOAD Events Storage PearDB Class.
 *
 * <p>This class is a {@link XOAD_Events_Storage} successor.</p>
 * <p>The class allows you to save events information in
 * PearDB compatible database.</p>
 * <p>Pear::DB supported databases (type parameter)</p>
 * - dbase 	- dBase
 * - fbsql 	- FrontBase
 * - ibase 	- InterBase
 * - ifx	- Informix
 * - msql	- Mini SQL
 * - mssql	- Microsoft SQL Server
 * - mysql	- MySQL (for servers running MySQL <= 4.0)
 * - mysqli	- MySQL (for servers running MySQL >= 4.1)
 * - oci8	- Oracle 7/8/9
 * - odbc	- Open Database Connectivity
 * - pgsql	- PostgreSQL
 * - sqlite	- SQLite
 * - sybase	- Sybase
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 * require_once('DB.php');
 *
 * require_once(XOAD_BASE . '/classes/events/storage/PearDB.class.php');
 *
 * $storage = new XOAD_Events_Storage_PearDB('type=?;server=?;user=?;password=?;database=?;[port=?]');
 *
 * $storage->postEvent('event', 'class');
 *
 * ?>
 * </code>
 *
 * @author		Norm 2782
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Events
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Events_Storage_PearDB extends XOAD_Events_Storage
{
	/**
	 * Initialize the static variable for the singleton pattern
	 *
	 * @var object
	 */
	static $instance = null;
	
	/**
	 * Holds the PearDB Database type setting used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $type;


	/**
	 * Holds the PearDB server used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $server;

	/**
	 * Holds the PearDB user used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $user;

	/**
	 * Holds the PearDB password used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $password;

	/**
	 * Holds the PearDB database used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $database;

	/**
	 * Holds the PearDB port used in the connection string.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $port;

	/**
	 * Holds the PearDB DSN for reconnection to database.
	 *
	 * @access	protected
	 *
	 * @var		resource
	 *
	 */
	public $pearDSN;

	/**
	 * Creates a new instance of the {@link XOAD_Events_Storage_PearDB} class.
	 *
	 * @access	public
	 *
	 * @param	string	$dsn	The data source name and parameters to use
	 *							when connecting with PearDB.
	 *
 	 */
	protected function __construct($dsn)
	{
		parent::__construct($dsn);

		$this->pearDSN = "{$this->type}://{$this->user}:{$this->password}@{$this->server}";

		if ( ! empty($this->port)) {

			$this->pearDSN .= ':' . $this->port;
		}

		$this->pearDSN .= '/' . $this->database;
	}

	/**
	 * Retrieves a static instance of the {@link XOAD_Events_Storage_PearDB} class.
	 *
	 * <p>This method overrides {@link XOAD_Events_Storage::getStorage}.</p>
	 *
	 * @access	public
	 *
	 * @param	string	$dsn	The data source name and parameters to use
	 *							when connecting with PearDB.
	 *
	 * @return	object	A static instance to the
	 *					{@link XOAD_Events_Storage_PearDB} class.
	 *
	 * @static
	 *
 	 */
	public static function &getStorage($dsn)
	{
		if ( ! isset(self::$instance)) {

			self::$instance = new XOAD_Events_Storage_PearDB($dsn);
		}

		return self::$instance;
	}

	/**
	 * Creates a PearDB connection link.
	 *
	 * @access	private
	 *
	 * @return	resource
	 *
	 */
	public function &getConnection()
	{
		$connection =& DB::connect($this->pearDSN);

		$connection->setFetchMode(DB_FETCHMODE_ASSOC);

		return $connection;
	}

	/**
	 * Closes a PearDB connection link.
	 *
	 * @access	private
	 *
	 * @return	void
	 *
	 */
	public function closeConnection(&$connection)
	{
		$connection->disconnect();

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
	public function escapeString($unescapedString, &$connection)
	{
		return $connection->escapeSimple($unescapedString);
	}

	/**
 	 * Posts multiple events to the database.
	 *
	 * <p>Valid keys for each event are:</p>
	 * - event		- the event name (case-sensitive);
	 * - className	- the class that is the source of the event;
	 * - sender		- the sender object of the event;
	 * - data		- the data associated with the event;
	 * - filter		- the event filter data (case-insensitive);
	 *				  using this key you can post events with
	 *				  the same name but with different filter data;
	 *				  the client will respond to them individually;
	 * - time		- the event start time (seconds since the Unix
	 *				  Epoch (January 1 1970 00:00:00 GMT);
	 * - lifetime	- the event lifetime (in seconds);
	 *
	 * @access	public
	 *
	 * @param	array	$eventsData		Array containing associative arrays
	 *									with information for each event.
	 *
	 * @return	bool	true on success, false otherwise.
	 *
	 */
	public function postMultipleEvents($eventsData)
	{
		$connection =& $this->getConnection();

		foreach ($eventsData as $event) {

			if ( ! parent::postMultipleEvents($event)) {

				continue;
			}

			$sqlQuery = '
				INSERT INTO
					`' . XOAD_EVENTS_TABLE_NAME . '`
				(
					`event`,
					`className`,
					`filter`,
					`sender`,
					`data`,
					`time`,
					`endTime`
				)
				VALUES
				(
					\'' . $this->escapeString($event['event'], $connection) . '\',
					\'' . $this->escapeString($event['className'], $connection) . '\',
			';

			if (isset($event['filter'])) {

				$sqlQuery .= '\'' . $this->escapeString($event['filter'], $connection) . '\',';

			} else {

				$sqlQuery .= 'NULL,';
			}

			if (isset($event['sender'])) {

				$sqlQuery .= '\'' . $this->escapeString(serialize($event['sender']), $connection) . '\',';

			} else {

				$sqlQuery .= 'NULL,';
			}

			if (isset($event['data'])) {

				$sqlQuery .= '\'' . $this->escapeString(serialize($event['data']), $connection) . '\',';

			} else {

				$sqlQuery .= 'NULL,';
			}

			$sqlQuery .= '
					' . $this->escapeString($event['time'], $connection) . ',
					' . $this->escapeString($event['time'] + $event['lifetime'], $connection) . '
				)';

			$connection->query($sqlQuery);
		}

		$this->closeConnection($connection);

		return true;
	}

	/**
 	 * Deletes old events from the database.
	 *
	 * <p>This method is called before calling {@link filterEvents}
	 * or {@link filterMultipleEvents} to delete all expired events
	 * from the database.</p>
	 *
	 * @access	public
	 *
	 * @return	bool	true on success, false otherwise.
	 *
	 */
	public function cleanEvents()
	{
		$connection =& $this->getConnection();

		$time = parent::cleanEvents();

		$sqlQuery = '
			DELETE FROM
				`' . XOAD_EVENTS_TABLE_NAME . '`
			WHERE
				`endTime` < ' . $this->escapeString($time, $connection) . '
		';

		$connection->query($sqlQuery);

		$this->closeConnection($connection);

		return true;
	}

	/**
 	 * Filters the events in the database using multiple criterias.
	 *
	 * <p>Valid keys for each event are:</p>
	 * - event		- the event name (case-sensitive);
	 * - className	- the class that is the source of the event;
	 * - filter		- the event filter data (case-insensitive);
	 * 				  using this argument you can filter events with
	 *				  the same name but with different filter data;
	 * - time		- the event start time (seconds since the Unix
	 *				  Epoch (January 1 1970 00:00:00 GMT).
	 *
	 * @access	public
	 *
	 * @param	array	$eventsData		Array containing associative arrays
	 *									with information for each event.
	 *
	 * @return	array	Sequental array that contains all events that match the
	 *					supplied criterias, ordered by time (ascending).
	 *
	 */
	public function filterMultipleEvents($eventsData)
	{
		$connection =& $this->getConnection();

		$sqlQuery = '
			SELECT
				`event`,
				`className`,
				`filter`,
				`sender`,
				`data`,
				`time`,
				`endTime`
			FROM
				`' . XOAD_EVENTS_TABLE_NAME . '`
			WHERE
		';

		$index = 0;

		$length = sizeof($eventsData);

		foreach ($eventsData as $event) {

			if ( ! parent::filterMultipleEvents($event)) {

				continue;
			}

			$sqlQuery .= '(
				`time` > ' . $this->escapeString($event['time'], $connection) . '
				AND
				`event` = \'' . $this->escapeString($event['event'], $connection) . '\'
				AND
				`className` = \'' . $this->escapeString($event['className'], $connection) . '\'
			';

			if (isset($event['filter'])) {

				$sqlQuery .= 'AND `filter` = \'' . $this->escapeString($event['filter'], $connection) . '\'';
			}

			if ($index < $length - 1) {

				$sqlQuery .= ') OR ';

			} else {

				$sqlQuery .= ')';
			}

			$index ++;
		}

		$sqlQuery .= '
			ORDER BY
				`time` ASC
		';

		$events = array();

		$sqlResult =& $connection->query($sqlQuery);

		while (($row =& $sqlResult->fetchRow()) != false) {

			$events[] = array(
			'event'		=>	$row['event'],
			'className'	=>	$row['className'],
			'filter'	=>	$row['filter'],
			'time'		=>	(float) $row['time'],
			'endTime'	=>	(float) $row['endTime'],
			'eventData'	=>	array(
			'sender'	=>	($row['sender'] === null ? null : unserialize($row['sender'])),
			'data'		=>	($row['data'] === null ? null : unserialize($row['data']))
			));
		}

		$sqlResult->free();

		$this->closeConnection($connection);

		return $events;
	}
}
?>