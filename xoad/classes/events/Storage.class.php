<?php
/**
 * XOAD Events Storage file.
 *
 * <p>This file defines the {@link XOAD_Events_Storage} Class.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * $storage =& XOAD_Events_Storage::getStorage();
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
 * XOAD Events Storage Class.
 *
 * <p>This class is used as base class for all XOAD Events storage providers.</p>
 * <p>The class also defines the {@link getStorage} method which
 * is used to retrieve an instane to the configurated storage.</p>
 * <p>Example XOAD Events provider: {@link XOAD_Events_Storage_MySQL}.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * define('XOAD_EVENTS_STORAGE_DSN', 'MySQL://server=?;user=?;password=?;database=?');
 *
 * require_once('xoad.php');
 *
 * // The line below will return a XOAD_Events_Storage_MySQL
 * // class instance.
 * $storage =& XOAD_Events_Storage::getStorage();
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
class XOAD_Events_Storage
{
	/**
	 * Initialize the static variable for the singleton pattern
	 *
	 * @var object
	 */
	static $instance = null;
	
	/**
	 * Creates a new instance of the {@link XOAD_Events_Storage} class.
	 *
	 * @access	public
	 *
	 * @param	string	$dsn	The data source name and parameters to use
	 *							when creating the instance.
	 *
 	 */
	protected function __construct($dsn = null)
	{
		if ( ! empty($dsn)) {

			$pairs = explode(';', $dsn);

			foreach ($pairs as $pair) {

				if ( ! empty($pair)) {

					list($key, $value) = explode('=', $pair, 2);

					$this->$key = $value;
				}
			}
		}
	}

	/**
 	 * Retrieves an instanse to the configurated XOAD Events storage provider.
	 *
	 * <p>Example:</p>
	 * <code>
	 * <?php
	 *
	 * require_once('xoad.php');
	 *
	 * $storage =& XOAD_Events_Storage::getStorage();
	 *
	 * $storage->postEvent('event', 'class');
	 *
	 * ?>
	 * </code>
	 *
	 * @access	public
	 *
	 * @return	object	Singleton {@link XOAD_Events_Storage} inherited class based
	 *					on the configuration (see {@link XOAD_EVENTS_STORAGE_DSN}).
	 *
	 * @static
	 *
	 */
	public static function &getStorage()
	{
		if ( ! isset(self::$instance)) {

			$className = null;

			$classParameters = null;

			$separator = '://';

			$position = strpos(XOAD_EVENTS_STORAGE_DSN, $separator);

			if ($position === false) {

				$className = XOAD_EVENTS_STORAGE_DSN;

			} else {

				$className = substr(XOAD_EVENTS_STORAGE_DSN, 0, $position);

				$classParameters = substr(XOAD_EVENTS_STORAGE_DSN, $position + strlen($separator));
			}

			if (empty($className)) {

				return null;
			}

			$fileName = XOAD_BASE . '/classes/events/storage/' . $className . '.class.php';
			
			/**
			 * Load the file that defines the events storage provider.
			 */
			require_once($fileName);

			$realClassName = 'XOAD_Events_Storage_' . $className;

			if ( ! class_exists($realClassName)) {

				return null;
			}

			self::$instance = new $realClassName($classParameters);
		}

		return self::$instance;
	}

	/**
 	 * Posts a single event to the storage.
	 *
	 * <p>The {@link $event} and {@link $class} arguments are required
	 * for each event. The {@link $sender}, {@link $data}, {@link $filter},
	 * {@link $time} and {@link $lifetime} arguments are optional.</p>
	 * <p>In case you have supplied both {@link $class} and {@link $sender},
	 * then the {@link $sender}'s class must match the one you've supplied.</p>
	 * <p>This method calls {@link postMultipleEvents} with the appropriate
	 * arguments.</p>
	 *
	 * @access	public
	 *
	 * @param	string	$event		The event name (case-sensitive).
	 *
	 * @param	string	$class		The class that is the source of the event.
	 *
	 * @param	object	$sender		The sender object of the event.
	 *
	 * @param	mixed	$data		The data associated with the event.
	 *
	 * @param	string	$filter		The event filter data (case-insensitive).
	 *								Using this argument you can post events with
	 *								the same name but with different filter data.
	 *								The client will respond to them individually.
	 *
	 * @param	int		$time		The event start time (seconds since the Unix
	 *								Epoch (January 1 1970 00:00:00 GMT).
	 *
	 * @param	int		$lifetime	The event lifetime (in seconds).
	 *
	 * @return	bool	true on success, false otherwise.
	 *
	 */
	public function postEvent($event, $class, $sender = null, $data = null, $filter = null, $time = null, $lifetime = null)
	{
		return $this->postMultipleEvents(array(array(
		'event'		=>	$event,
		'className'	=>	$class,
		'sender'	=>	$sender,
		'data'		=>	$data,
		'filter'	=>	$filter,
		'time'		=>	$time,
		'lifetime'	=>	$lifetime
		)));
	}

	/**
 	 * This method should be called from each successor to add
 	 * common data to the event.
 	 *
 	 * <p>When you call this method you should pass an associative
 	 * array that contains the event data. This method will populate
 	 * it with the missing information and will check the validity of
 	 * the presented.</p>
	 *
	 * @access	public
	 *
	 * @return	bool	true on success, false otherwise
	 *
	 */
	public function postMultipleEvents(&$event)
	{
		if (( ! isset($event['time'])) || ($event['time'] === null)) {

			$event['time'] = XOAD_Utilities::getMicroTime();
		}

		if (( ! isset($event['lifetime'])) || ($event['lifetime'] === null)) {

			$event['lifetime'] = XOAD_EVENTS_LIFETIME;
		}

		if ((isset($event['sender'])) && ($event['sender'] !== null)) {

			if (XOAD_Utilities::getType($event['sender']) != 'object') {

				return false;
			}

			if (strcasecmp($event['className'], get_class($event['sender'])) != 0) {

				return false;
			}
		}

		return true;
	}

	/**
 	 * This method should be called from each successor to retrieve
 	 * the start time of the old events in the storage.
 	 *
	 * @access	public
	 *
	 * @return	int		The start time of the old events.
	 *
	 */
	public function cleanEvents()
	{
		return XOAD_Utilities::getMicroTime() - XOAD_EVENTS_LIFETIME;
	}

	/**
 	 * Filters the events in the storage using a single criteria.
	 *
	 * <p>The {@link $event} and {@link $class} arguments are required
	 * for each event. The {@link $filter} and {@link $time} arguments
	 * are optional.</p>
	 * <p>This method calls {@link filterMultipleEvents} with the appropriate
	 * arguments.</p>
	 *
	 * @access	public
	 *
	 * @param	string	$event		The event name (case-sensitive).
	 *
	 * @param	string	$class		The class that is the source of the event.
	 *
	 * @param	string	$filter		The event filter data (case-insensitive).
	 *								Using this argument you can filter events with
	 *								the same name but with different filter data.
	 *
	 * @param	int		$time		The event start time (seconds since the Unix
	 *								Epoch (January 1 1970 00:00:00 GMT).
	 *
	 * @return	array	Sequental array that contains all events that match the
	 *					supplied criterias, ordered by time (ascending).
	 *
	 */
	public function filterEvents($event, $class, $filter = null, $time = null)
	{
		return $this->filterMultipleEvents(array(array(
		'event'		=>	$event,
		'className'	=>	$class,
		'filter'	=>	$filter,
		'time'		=>	$time
		)));
	}

	/**
 	 * This method should be called from each successor to add
 	 * common data to the event.
 	 *
 	 * <p>When you call this method you should pass an associative
 	 * array that contains the event data. This method will populate
 	 * it with the missing information and will check the validity of
 	 * the presented.</p>
	 *
	 * @access	public
	 *
	 * @return	bool	true on success, false otherwise
	 *
	 */
	public function filterMultipleEvents(&$event)
	{
		if (( ! isset($event['time'])) || ($event['time'] === null)) {

			$event['time'] = XOAD_Utilities::getMicroTime();
		}

		return true;
	}
}
?>