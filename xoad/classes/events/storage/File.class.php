<?php
/**
 * XOAD Events File Provider file.
 *
 * <p>This file defines the {@link XOAD_Events_Storage_File} Class.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * require_once(XOAD_BASE . '/classes/events/storage/File.class.php');
 *
 * $storage = new XOAD_Events_Storage_File(['container=filename']);
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
 * XOAD Events Storage File Class.
 *
 * <p>This class is a {@link XOAD_Events_Storage} successor.</p>
 * <p>The class allows you to save events information in a
 * flat file.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * require_once(XOAD_BASE . '/classes/events/storage/File.class.php');
 *
 * $storage = new XOAD_Events_Storage_File(['container=filename']);
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
class XOAD_Events_Storage_File extends XOAD_Events_Storage
{
	/**
	 * Initialize the static variable for the singleton pattern
	 *
	 * @var object
	 */
	static $instance = null;
	
	/**
	 * Holds the file name where events information is saved.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $container = 'EVENTS';

	/**
	 * Holds the separator used in the container.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $separator = "\t***\t";

	/**
	 * Creates a new instance of the {@link XOAD_Events_Storage_File} class.
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
	 * Retrieves a static instance of the {@link XOAD_Events_Storage_File} class.
	 *
	 * <p>This method overrides {@link XOAD_Events_Storage::getStorage}.</p>
	 *
	 * @access	public
	 *
	 * @param	string	$dsn	The data source name and parameters to use
	 *							when retrieving the instance.
	 *
	 * @return	object	A static instance to the
	 *					{@link XOAD_Events_Storage_File} class.
	 *
	 * @static
	 *
 	 */
	public static function &getStorage($dsn)
	{
		if ( ! isset(self::$instance)) {

			self::$instance = new XOAD_Events_Storage_File($dsn);
		}

		return self::$instance;
	}

	/**
	 * Gets the absolute path to the container.
	 *
	 * @access	private
	 *
	 * @return	string
	 *
	 */
	public function getFileName()
	{
		if (strpos($this->container, DIRECTORY_SEPARATOR) === 0) {

			return $this->container;
		}

		if (strlen($this->container) >= 3) {

			if (
			($this->container{1} == ':') &&
			($this->container{2} == DIRECTORY_SEPARATOR)) {

				return $this->container;
			}
		}

		return XOAD_BASE . '/var/' . $this->container;
	}

	/**
 	 * Posts multiple events to the container.
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
		$handle = @fopen($this->getFileName(), 'a');

		if ( ! $handle) {

			return false;
		}

		@ignore_user_abort(true);

		flock($handle, LOCK_EX);

		foreach ($eventsData as $event) {

			if ( ! parent::postMultipleEvents($event)) {

				continue;
			}

			$row = $event['event'];
			$row .= $this->separator;

			$row .= $event['className'];
			$row .= $this->separator;

			$row .= $event['time'];
			$row .= $this->separator;

			$row .= $event['time'] + $event['lifetime'];
			$row .= $this->separator;

			$row .= isset($event['filter']) ? serialize($event['filter']) : 'N;';
			$row .= $this->separator;

			$rowData = array();

			if (isset($event['sender'])) {

				$rowData['sender'] = $event['sender'];
			}

			if (isset($event['data'])) {

				$rowData['data'] = $event['data'];
			}

			$row .= serialize($rowData);

			$row .= "\n";

			fwrite($handle, $row);
		}

		flock($handle, LOCK_UN);

		fclose($handle);

		@ignore_user_abort(false);

		return true;
	}

	/**
 	 * Deletes old events from the container.
	 *
	 * <p>This method is called before calling {@link filterEvents}
	 * or {@link filterMultipleEvents} to delete all expired events
	 * from the container.</p>
	 *
	 * @access	public
	 *
	 * @return	bool	true on success, false otherwise.
	 *
	 */
	public function cleanEvents()
	{
		$fileName = $this->getFileName();

		$containerData = @file($fileName);

		if (empty($containerData)) {

			return true;
		}

		$handle = @fopen($fileName, 'a+');

		if ( ! $handle) {

			return false;
		}

		$time = parent::cleanEvents();

		@ignore_user_abort(true);

		flock($handle, LOCK_EX);

		ftruncate($handle, 0);

		foreach ($containerData as $row) {

			list($dummy, $dummy, $dummy, $endTime, $dummy) = explode($this->separator, $row, 5);

			$endTime = (double) $endTime;

			if ($endTime >= $time) {

				fwrite($handle, $row);
			}
		}

		flock($handle, LOCK_UN);

		fclose($handle);

		@ignore_user_abort(false);

		return true;
	}

	/**
 	 * Filters the events in the container using multiple criterias.
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
		$events = array();

		$containerData = @file($this->getFileName());

		if (empty($containerData)) {

			return $events;
		}

		foreach ($eventsData as $event) {

			if ( ! parent::filterMultipleEvents($event)) {

				continue;
			}

			foreach ($containerData as $row) {

				list($eventName, $className, $time, $endTime, $filter, $rowData) = explode($this->separator, $row, 6);

				$filter = unserialize($filter);

				$match = (strcmp($eventName, $event['event']) == 0);
				$match &= (strcasecmp($className, $event['className']) == 0);
				$match &= ($time > $event['time']);

				if (isset($event['filter'])) {

					$match &= ($filter == $event['filter']);
				}

				if ($match) {

					$rowData = unserialize($rowData);

					$events[] = array(
					'event'		=>	$eventName,
					'className'	=>	$className,
					'filter'	=>	$filter,
					'time'		=>	(double) $time,
					'endTime'	=>	(double) $endTime,
					'eventData'	=>	array(
					'sender'	=>	(isset($rowData['sender']) ? $rowData['sender'] : null),
					'data'		=>	(isset($rowData['data']) ? $rowData['data'] : null)
					));
				}
			}
		}

		if ( ! empty($events)) {

			usort($events, array('XOAD_Events_Storage_File', 'sortEvents'));
		}

		return $events;
	}

	/**
 	 * Callback to sort events by time.
	 *
	 * @access	public
	 *
	 * @param	array	$a	Data associated with the first event.
	 *
	 * @param	array	$b	Data associated with the second event.
	 *
	 * @return	int		Less than, equal to, or greater than zero if the first
	 *					event's time is considered to be respectively less than,
	 *					equal to, or greater than the second event's time.
	 *
	 */
	public function sortEvents($a, $b)
	{
		if ($a['time'] == $b['time']) {

			return 0;
		}

		return (($a['time'] < $b['time']) ? -1 : 1);
	}

}
?>