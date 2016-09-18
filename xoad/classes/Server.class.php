<?php
/**
 * XOAD Server file.
 *
 * <p>This file defines the {@link XOAD_Server} Class.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * class Calculator
 * {
 * 	var $result;
 *
 * 	function Calculator()
 * 	{
 * 		$this->result = 0;
 * 	}
 *
 * 	function Add($arg)
 * 	{
 * 		$this->result += $arg;
 * 	}
 * }
 *
 * XOAD_Server::runServer();
 *
 * ?>
 * </code>
 *
 * @author	Stanimir Angeloff
 *
 * @package	XOAD
 *
 * @version	0.6.0.0
 *
 */

/**
 * XOAD Server Class.
 *
 * <p>This class is used to handle client callbacks.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * class Calculator
 * {
 * 	var $result;
 *
 * 	function Calculator()
 * 	{
 * 		$this->result = 0;
 * 	}
 *
 * 	function Add($arg)
 * 	{
 * 		$this->result += $arg;
 * 	}
 * }
 *
 * XOAD_Server::runServer();
 *
 * ?>
 * </code>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Server extends XOAD_Observable
{
	/**
	 * Checks if the request is a client callback
	 * to the server and handles it.
	 *
	 * @access	public
	 *
	 * @return	bool	true if the request is a valid client callback,
	 *					false otherwise.
	 *
	 * @static
	 *
	 */
	public static function runServer()
	{
		if ( ! XOAD_Server::notifyObservers('runServerEnter')) {

			return false;
		}

		if (XOAD_Server::initializeCallback()) {

			XOAD_Server::dispatch();

			/**
			 * Defines whether the request is a client callback.
			 */
			define('XOAD_CALLBACK', true);
		}

		if ( ! defined('XOAD_CALLBACK')) {

			/**
			 * Defines whether the request is a client callback.
			 */
			define('XOAD_CALLBACK', false);
		}

		if (XOAD_Server::notifyObservers('runServerLeave', array('isCallback' => XOAD_CALLBACK))) {

			return XOAD_CALLBACK;

		} else {

			return false;
		}
	}

	/**
	 * Checks if the request is a client callback to the
	 * server and initializes callback parameters.
	 *
	 * @access	public
	 *
	 * @return	bool	true if the request is a valid client callback,
	 *					false otherwise.
	 *
	 * @static
	 *
	 */
	public static function initializeCallback()
	{
		if ( ! XOAD_Server::notifyObservers('initializeCallbackEnter')) {

			return false;
		}

		if (isset($_GET['xoadCall'])) {

			if (strcasecmp($_GET['xoadCall'], 'true') == 0) {

				/* By Camilo Carromeu at september 17, 2016 - Deprecated code for PHP 7
				if ( ! isset($GLOBALS['HTTP_RAW_POST_DATA'])) {

					return false;
				}

				$requestBody = @unserialize($GLOBALS['HTTP_RAW_POST_DATA']);
				*/

				$requestBody = @unserialize (file_get_contents ("php://input"));

				if ($requestBody == null) {

					return false;
				}

				if (
				isset($requestBody['eventPost']) &&
				isset($requestBody['className']) &&
				isset($requestBody['sender']) &&
				isset($requestBody['event']) &&
				array_key_exists('data', $requestBody) &&
				array_key_exists('filter', $requestBody)) {

					if (
					(XOAD_Utilities::getType($requestBody['eventPost']) != 'bool') ||
					(XOAD_Utilities::getType($requestBody['className']) != 'string') ||
					(XOAD_Utilities::getType($requestBody['sender']) != 'string') ||
					(XOAD_Utilities::getType($requestBody['event']) != 'string')) {

						return false;
					}

					if ( ! empty($requestBody['className'])) {

						XOAD_Server::loadClass($requestBody['className']);

					} else {

						return false;
					}

					if ( ! XOAD_Server::isClassAllowed($requestBody['className'])) {

						return false;
					}

					$requestBody['sender'] = @unserialize($requestBody['sender']);

					if ($requestBody['sender'] === null) {

						return false;
					}

					if (strcasecmp(get_class($requestBody['sender']), $requestBody['className']) != 0) {

						return false;
					}

					if ( ! XOAD_Server::notifyObservers('initializeCallbackSuccess', array('request' => &$requestBody))) {

						return false;
					}

					$GLOBALS['_XOAD_SERVER_REQUEST_BODY'] =& $requestBody;

					if (XOAD_Server::notifyObservers('initializeCallbackLeave', array('request' => &$requestBody))) {

						return true;
					}

				} else if (
				isset($requestBody['eventsCallback']) &&
				isset($requestBody['time']) &&
				isset($requestBody['data'])) {

					if (
					(XOAD_Utilities::getType($requestBody['eventsCallback']) != 'bool') ||
					(XOAD_Utilities::getType($requestBody['time']) != 'float') ||
					(XOAD_Utilities::getType($requestBody['data']) != 's_array')) {

						return false;
					}

					foreach ($requestBody['data'] as $eventData) {

						if ( ! empty($eventData['className'])) {

							XOAD_Server::loadClass($eventData['className']);

						} else {

							return false;
						}

						if ( ! XOAD_Server::isClassAllowed($eventData['className'])) {

							return false;
						}
					}

					if ( ! XOAD_Server::notifyObservers('initializeCallbackSuccess', array('request' => &$requestBody))) {

						return false;
					}

					$GLOBALS['_XOAD_SERVER_REQUEST_BODY'] =& $requestBody;

					if (XOAD_Server::notifyObservers('initializeCallbackLeave', array('request' => &$requestBody))) {

						return true;
					}

				} else {

					if (
					( ! isset($requestBody['source'])) ||
					( ! isset($requestBody['className'])) ||
					( ! isset($requestBody['method'])) ||
					( ! isset($requestBody['arguments']))) {

						return false;
					}

					if ( ! empty($requestBody['className'])) {

						XOAD_Server::loadClass($requestBody['className']);
					}

					$requestBody['source'] = @unserialize($requestBody['source']);

					$requestBody['arguments'] = @unserialize($requestBody['arguments']);

					if (
					($requestBody['source'] === null) ||
					($requestBody['className'] === null) ||
					($requestBody['arguments'] === null)) {

						return false;
					}

					if (
					(XOAD_Utilities::getType($requestBody['source']) != 'object') ||
					(XOAD_Utilities::getType($requestBody['className']) != 'string') ||
					(XOAD_Utilities::getType($requestBody['method']) != 'string') ||
					(XOAD_Utilities::getType($requestBody['arguments']) != 's_array')) {

						return false;
					}

					if (strcasecmp($requestBody['className'], get_class($requestBody['source'])) != 0) {

						return false;
					}

					if ( ! XOAD_Server::isClassAllowed($requestBody['className'])) {

						return false;
					}

					if (method_exists($requestBody['source'], XOAD_CLIENT_METADATA_METHOD_NAME)) {

						call_user_func_array(array(&$requestBody['source'], XOAD_CLIENT_METADATA_METHOD_NAME), array ());

						if (isset($requestBody['source']->xoadMeta)) {

							if (XOAD_Utilities::getType($requestBody['source']->xoadMeta) == 'object') {

								if (strcasecmp(get_class($requestBody['source']->xoadMeta), 'XOAD_Meta') == 0) {

									if ( ! $requestBody['source']->xoadMeta->isPublicMethod($requestBody['method'])) {

										return false;
									}
								}
							}
						}
					}

					if ( ! XOAD_Server::notifyObservers('initializeCallbackSuccess', array('request' => &$requestBody))) {

						return false;
					}

					$GLOBALS['_XOAD_SERVER_REQUEST_BODY'] =& $requestBody;

					if (XOAD_Server::notifyObservers('initializeCallbackLeave', array('request' => &$requestBody))) {

						return true;
					}
				}
			}
		}

		XOAD_Server::notifyObservers('initializeCallbackLeave');

		return false;
	}

	/**
	 * Dispatches a client callback to the server.
	 *
	 * @access	public
	 *
	 * @return	string	Outputs JavaString code that contains the result
	 *					and the output of the callback.
	 *
	 * @static
	 *
	 */
	public static function dispatch()
	{
		if (empty($GLOBALS['_XOAD_SERVER_REQUEST_BODY'])) {

			return false;
		}

		$requestBody =& $GLOBALS['_XOAD_SERVER_REQUEST_BODY'];

		if ( ! XOAD_Server::notifyObservers('dispatchEnter', array('request' => &$requestBody))) {

			return false;
		}

		if (isset($requestBody['eventPost'])) {

			$callbackResponse = array();

			$storage =& XOAD_Events_Storage::getStorage();

			$callbackResponse['status'] = $storage->postEvent($requestBody['event'], $requestBody['className'], $requestBody['sender'], $requestBody['data'], $requestBody['filter']);

			if (XOAD_Server::notifyObservers('dispatchLeave', array('request' => &$requestBody, 'response' => &$callbackResponse))) {

				if ( ! empty($callbackResponse['status'])) {

					print XOAD_Client::register($callbackResponse);
				}
			}

		} else if (isset($requestBody['eventsCallback'])) {

			$eventsQuery = array();

			foreach ($requestBody['data'] as $event) {

				$eventsQuery[] = array(
				'event'		=>	$event['event'],
				'className'	=>	$event['className'],
				'filter'	=>	$event['filter'],
				'time'		=>	$requestBody['time']
				);
			}

			$callbackResponse = array();

			$storage =& XOAD_Events_Storage::getStorage();

			$storage->cleanEvents();

			$callbackResponse['result'] = $storage->filterMultipleEvents($eventsQuery);

			if (XOAD_Server::notifyObservers('dispatchLeave', array('request' => &$requestBody, 'response' => &$callbackResponse))) {

				if ( ! empty($callbackResponse['result'])) {

					print XOAD_Client::register($callbackResponse);
				}
			}

		} else {

			$callbackResponse = array();

			$outputBuffering = @ob_start();

			set_error_handler(array('XOAD_Server', 'handleError'));

			$callbackResponse['returnValue'] = call_user_func_array(array(&$requestBody['source'], $requestBody['method']), $requestBody['arguments']);

			if (defined('XOAD_SERVER_EXCEPTION')) {

				if (XOAD_Server::notifyObservers('dispatchFailed', array('request' => &$requestBody, 'message' => XOAD_SERVER_EXCEPTION))) {

					XOAD_Server::throwException(XOAD_SERVER_EXCEPTION);

					return false;
				}
			}

			$callbackResponse['returnObject'] =& $requestBody['source'];

			if ($outputBuffering) {

				$output = @ob_get_contents();

				if ( ! empty($output)) {

					$callbackResponse['output'] = $output;
				}

				@ob_end_clean();
			}

			restore_error_handler();

			if (XOAD_Server::notifyObservers('dispatchLeave', array('request' => &$requestBody, 'response' => &$callbackResponse))) {

				print XOAD_Client::register($callbackResponse);
			}
		}

		return true;
	}

	/**
	 * Handles all errors that occur during the callback.
	 *
	 * <p>Only E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR and E_USER_ERROR
	 * will halt the callback and throw an exception.</p>
	 *
	 * @access	protected
	 *
	 * @param	int		$type		Error type (compile, core, user...).
	 *
	 * @param	string	$message	Error message.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function handleError($type, $message)
	{
		if (error_reporting()) {

			if ( ! XOAD_Server::notifyObservers('handleErrorEnter', array('type' => &$type, 'message' => &$message))) {

				return false;
			}

			$breakLevel = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;

			if (($type & $breakLevel) > 0) {

				if ( ! defined('XOAD_SERVER_EXCEPTION')) {

					/**
					 * Defines the error message that caused the callback to halt.
					 */
					define('XOAD_SERVER_EXCEPTION', $message);
				}
			}
		}

		XOAD_Server::notifyObservers('handleErrorLeave', array('type' => &$type, 'message' => &$message));

		return true;
	}

	/**
	 * Throws a XOAD callback exception.
	 *
	 * @access	protected
	 *
	 * @param	string	$message	Exception message.
	 *
	 * @return	string	Outputs JavaString code that contains the
	 *					exception message.
	 *
	 * @static
	 *
	 */
	public static function throwException($message)
	{
		if ( ! XOAD_Server::notifyObservers('throwExceptionEnter', array('message' => &$message))) {

			return false;
		}

		restore_error_handler();

		$callbackException = array();

		$callbackException['exception'] = $message;

		if (XOAD_Server::notifyObservers('throwExceptionLeave', array('message' => &$message))) {

			print XOAD_Client::register($callbackException);
		}

		return true;
	}

	/**
	 * Adds a specified class to the classes map.
	 *
	 * <p>Example:</p>
	 * <code>
	 * <?php
	 *
	 * require_once('xoad.php');
	 *
	 * XOAD_Server::mapClass('Calculator', 'Calculator.class.php');
	 *
	 * XOAD_Server::mapClass('EnglishDictionary', array('BaseDictionary.class.php', 'EnglishDictionary.class.php'));
	 *
	 * XOAD_Server::runServer();
	 *
	 * ?>
	 * </code>
	 *
	 * @access	public
	 *
	 * @param	string	$className	The class name to add.
	 *
	 * @param	mixed	$files		The files that are required
	 *								to load the class.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function mapClass($className, $files)
	{
		if ( ! isset($GLOBALS['_XOAD_SERVER_CLASSES_MAP'])) {

			$GLOBALS['_XOAD_SERVER_CLASSES_MAP'] = array();
		}

		$GLOBALS['_XOAD_SERVER_CLASSES_MAP'][strtolower($className)] = $files;
	}

	/**
	 * Loads a specified class from the classes map.
	 *
	 * @access	public
	 *
	 * @param	string	$className	The class name to load. Note that all files
	 *								that are included in the class map will be
	 *								loaded.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function loadClass($className)
	{
		$className = strtolower($className);

		if ( ! empty($GLOBALS['_XOAD_SERVER_CLASSES_MAP'])) {

			if (isset($GLOBALS['_XOAD_SERVER_CLASSES_MAP'][$className])) {

				$files = $GLOBALS['_XOAD_SERVER_CLASSES_MAP'][$className];

				$filesType = XOAD_Utilities::getType($files);

				if ($filesType == 'string') {

					require_once($files);

				} else if (
				($filesType == 's_array') ||
				($filesType == 'a_array')) {

					foreach ($files as $fileName) {

						require_once($fileName);
					}
				}
			}
		}
	}

	/**
	 * Adds specified classes to the allowed classes map.
	 *
	 * <p>Example:</p>
	 * <code>
	 * <?php
	 *
	 * class AllowedClass
	 * {
	 * 	function call() { return 'AllowedClass->call()'; }
	 * }
	 *
	 * class DeniedClass
	 * {
	 * 	function call() { return 'DeniedClass->call()'; }
	 * }
	 *
	 * require_once('xoad.php');
	 *
	 * XOAD_Server::allowClasses('AllowedClass');
	 *
	 * if (XOAD_Server::runServer()) {
	 *
	 * 	exit;
	 * }
	 *
	 * ?>
	 * <?= XOAD_Utilities::header() ?>
	 *
	 * <script type="text/javascript">
	 *
	 * var allowedClass = <?= XOAD_Client::register(new AllowedClass()) ?>;
	 *
	 * var deniedClass = <?= XOAD_Client::register(new DeniedClass()) ?>;
	 *
	 * alert(allowedClass.call());
	 *
	 * // This line will throw an exception.
	 * // DeniedClass is not in the allowed classes list.
	 * alert(deniedClass.call());
	 *
	 * </script>
	 * </code>
	 *
	 * @access	public
	 *
	 * @param	mixed	$classes	The classes that can be accessed within
	 *								a callback request.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function allowClasses($classes)
	{
		$classesType = XOAD_Utilities::getType($classes);

		if ( ! isset($GLOBALS['_XOAD_SERVER_ALLOWED_CLASSES'])) {

			$GLOBALS['_XOAD_SERVER_ALLOWED_CLASSES'] = array();
		}

		$allowedClasses =& $GLOBALS['_XOAD_SERVER_ALLOWED_CLASSES'];

		if ($classesType == 'string') {

			$allowedClasses[] = strtolower($classes);

		} else if (($classesType == 's_array') || ($classesType == 'a_array')) {

			foreach ($classes as $class) {

				$allowedClasses[] = strtolower($class);
			}
		}
	}

	/**
	 * Adds specified classes to the denied classes map.
	 *
	 * <p>Example:</p>
	 * <code>
	 * <?php
	 *
	 * class AllowedClass
	 * {
	 * 	function call() { return 'AllowedClass->call()'; }
	 * }
	 *
	 * class DeniedClass
	 * {
	 * 	function call() { return 'DeniedClass->call()'; }
	 * }
	 *
	 * require_once('xoad.php');
	 *
	 * XOAD_Server::denyClasses('DeniedClass');
	 *
	 * if (XOAD_Server::runServer()) {
	 *
	 * 	exit;
	 * }
	 *
	 * ?>
	 * <?= XOAD_Utilities::header() ?>
	 *
	 * <script type="text/javascript">
	 *
	 * var allowedClass = <?= XOAD_Client::register(new AllowedClass()) ?>;
	 *
	 * var deniedClass = <?= XOAD_Client::register(new DeniedClass()) ?>;
	 *
	 * alert(allowedClass.call());
	 *
	 * // This line will throw an exception.
	 * // DeniedClass is in the denied classes list.
	 * alert(deniedClass.call());
	 *
	 * </script>
	 * </code>
	 *
	 * @access	public
	 *
	 * @param	mixed	$classes	The classes that can NOT be accessed
	 *								within a callback request.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function denyClasses($classes)
	{
		$classesType = XOAD_Utilities::getType($classes);

		if ( ! isset($GLOBALS['_XOAD_SERVER_DENIED_CLASSES'])) {

			$GLOBALS['_XOAD_SERVER_DENIED_CLASSES'] = array();
		}

		$deniedClasses =& $GLOBALS['_XOAD_SERVER_DENIED_CLASSES'];

		if ($classesType == 'string') {

			$deniedClasses[] = strtolower($classes);

		} else if (($classesType == 's_array') || ($classesType == 'a_array')) {

			foreach ($classes as $class) {

				$deniedClasses[] = strtolower($class);
			}
		}
	}

	/**
	 * Checks if a class can be accessed within a callback request.
	 *
	 * @access	public
	 *
	 * @param	string	$class	The class name to check.
	 *
	 * @return	bool	true if the class can be accessed, false if
	 *					the class is denied and can NOT be accessed.
	 *
	 * @static
	 *
	 */
	public static function isClassAllowed($class)
	{
		$allowedClasses = null;

		$deniedClasses = null;

		if (isset($GLOBALS['_XOAD_SERVER_ALLOWED_CLASSES'])) {

			$allowedClasses =& $GLOBALS['_XOAD_SERVER_ALLOWED_CLASSES'];
		}

		if (isset($GLOBALS['_XOAD_SERVER_DENIED_CLASSES'])) {

			$deniedClasses =& $GLOBALS['_XOAD_SERVER_DENIED_CLASSES'];
		}

		if ( ! empty($deniedClasses)) {

			if (in_array(strtolower($class), $deniedClasses)) {

				return false;
			}
		}

		if ( ! empty($allowedClasses)) {

			if ( ! in_array(strtolower($class), $allowedClasses)) {

				return false;
			}
		}

		return true;
	}

	/**
	 * Adds a {@link XOAD_Server} events observer.
	 *
	 * @access	public
	 *
	 * @param	mixed	$observer	The observer object to add (must extend {@link XOAD_Observer}).
	 *
	 * @return	string	true on success, false otherwise.
	 *
	 * @static
	 *
	 */
	public static function addObserver(&$observer, $className = 'XOAD_Server')
	{
		return parent::addObserver($observer, $className);
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	bool
	 *
	 */
	public static function notifyObservers($event = 'default', $arg = null, $className = 'XOAD_Server')
	{
		return parent::notifyObservers($event, $arg, $className);
	}
}
?>
