<?php
/**
 * XOAD Events MySQL Provider file.
 *
 * <p>This file defines the {@link XOAD_Events_Storage_MySQL} Class.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * require_once(XOAD_BASE . '/classes/events/storage/MySQL.class.php');
 *
 * $storage = new XOAD_Events_Storage_MySQL('server=?;user=?;password=?;database=?;[port=?]');
 *
 * $storage->postEvent('event', 'class');
 *
 * ?>
 * </code>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Events
 *
 * @version		0.6.0.0
 *
 */

/**
 * Defines the table name in the MySQL database that is used to save the
 * events information (default value: xoad_events).
 *
 * @ignore
 *
 */
define('XOAD_EVENTS_TABLE_NAME', 'xoad_events');

/**
 * XOAD Events Storage MySQL Class.
 *
 * <p>This class is a {@link XOAD_Events_Storage} successor.</p>
 * <p>The class allows you to save events information in
 * MySQL database.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * require_once(XOAD_BASE . '/classes/events/storage/MySQL.class.php');
 *
 * $storage = new XOAD_Events_Storage_MySQL('server=?;user=?;password=?;database=?;[port=?]');
 *
 * $storage->postEvent('event', 'class');
 *
 * ?>
 * </code>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Events
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Events_Storage_MySQL extends XOAD_Events_Storage
{
	/**
	 * Initialize the static variable for the singleton pattern
	 *
	 * @var object
	 */
	static $instance = null;
	
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
	 * Creates a new instance of the {@link XOAD_Events_Storage_MySQL} class.
	 *
	 * @access	public
	 *
	 * @param	string	$dsn	The data source name and parameters to use
	 *							when connecting to MySQL.
	 *
 	 */
	protected function __construct($dsn)
	{
		parent::__construct($dsn);
	}

	/**
	 * Retrieves a static instance of the {@link XOAD_Events_Storage_MySQL} class.
	 *
	 * <p>This method overrides {@link XOAD_Events_Storage::getStorage}.</p>
	 *
	 * @access	public
	 *
	 * @param	string	$dsn	The data source name and parameters to use
	 *							when connecting to MySQL.
	 *
	 * @return	object	A static instance to the
	 *					{@link XOAD_Events_Storage_MySQL} class.
	 *
	 * @static
	 *
 	 */
	public static function &getStorage($dsn)
	{
		if ( ! isset(self::$instance)) {

			self::$instance = new XOAD_Events_Storage_MySQL($dsn);
		}

		return self::$instance;
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

			mysql_query($sqlQuery, $connection);
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

		mysql_query($sqlQuery, $connection);

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

		$sqlResult = mysql_query($sqlQuery, $connection);

		while (($row = mysql_fetch_assoc($sqlResult)) !== false) {

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

		mysql_free_result($sqlResult);

		$this->closeConnection($connection);

		return $events;
	}
}
?>