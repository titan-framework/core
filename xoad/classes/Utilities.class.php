<?php
/**
 * XOAD Utilities file.
 *
 * <p>This file defines the {@link XOAD_Utilities} Class.</p>
 *
 * @author	Stanimir Angeloff
 *
 * @package	XOAD
 *
 * @version	0.6.0.0
 *
 */

/**
 * XOAD Utilities Class.
 *
 * <p>This class defines extended functions that
 * the XOAD package uses and overrides some
 * deprecated functions, like gettype(...).</p>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Utilities extends XOAD_Observable
{
	/**
	 * Checks if an array is an associative array.
	 *
	 * @access	public
	 *
	 * @param	mixed	$var	The array to check.
	 *
	 * @return	bool	true if {@link $var} is an associative array, false
	 *					if {@link $var} is a sequential array.
	 *
	 * @static
	 *
	 */
	public static function isAssocArray($var)
	{
		// This code is based on mike-php's
		// comment in is_array function documentation.
		//
		// http://bg.php.net/is_array
		//
		// Thank you.
		//

		if ( ! is_array($var)) {

			return false;
		}

		$arrayKeys = array_keys($var);

		$sequentialKeys = range(0, sizeof($var));

		if (function_exists('array_diff_assoc')) {

			if (array_diff_assoc($arrayKeys, $sequentialKeys)) {

				return true;
			}

		} else {

			if (
			(array_diff($arrayKeys, $sequentialKeys)) &&
			(array_diff($sequentialKeys, $arrayKeys))) {

				return true;
			}
		}

		return false;
	}

	/**
	 * Gets the type of a variable.
	 *
	 * @access	public
	 *
	 * @param	mixed	$var	The source variable.
	 *
	 * @return	string	Possibles values for the returned string are:
	 *					- "bool"
	 *					- "int"
	 *					- "float"
	 *					- "string"
	 *					- "s_array"
	 *					- "a_array"
	 *					- "object"
	 *					- "null"
	 *					- "unknown"
	 *
	 * @static
	 *
	 */
	public static function getType($var)
	{
		if (is_bool($var)) {

			return 'bool';

		} else if (is_int($var)) {

			return 'int';

		} else if (is_float($var)) {

			return 'float';

		} else if (is_string($var)) {

			return 'string';

		} else if (is_array($var)) {

			if (XOAD_Utilities::isAssocArray($var)) {

				return 'a_array';

			} else {

				return 's_array';
			}

		} else if (is_object($var)) {

			return 'object';

		} else if (is_null($var)) {

			return 'null';
		}

		return 'unknown';
	}

	/**
	 * Return current UNIX timestamp with microseconds.
	 *
	 * @access	public
	 *
	 * @return	float	Returns the float 'sec,msec' where 'sec' is the
	 *					current time measured in the number of seconds since
	 *					the Unix Epoch (0:00:00 January 1, 1970 GMT), and
	 *					'msec' is the microseconds part.
	 *
	 * @static
	 *
	 */
	public static function getMicroTime()
	{
		list($microTime, $time) = explode(" ", microtime());

		return ((float) $microTime + (float) $time);
	}

	/**
	 * Returns the URL for the current request (includings
	 * the query string).
	 *
	 * @access	public
	 *
	 * @return	string	Current request URL.
	 *
	 * @static
	 *
	 */
	public static function getRequestUrl()
	{
		$url = $_SERVER['PHP_SELF'];

		if ( ! empty($_SERVER['QUERY_STRING'])) {

			$url .= '?' . $_SERVER['QUERY_STRING'];
		}

		return $url;
	}

	/**
	 * Registers XOAD client header files.
	 *
	 * @access	public
	 *
	 * @param	string	$base		Base XOAD folder.
	 *
	 * @param	bool	$optimized	true to include optimized headers, false otherwise.
	 *
	 * @return	string	HTML code to include XOAD client files.
	 *
	 * @static
	 *
	 */
	public static function header($base = '.', $optimized = true)
	{
		$returnValue = '<script type="text/javascript" src="' . $base . '/js/';

		$returnValue .= 'xoad';

		if ($optimized) {

			$returnValue .= '_optimized';
		}

		$returnValue .= '.js"></script>';

		if (array_key_exists('_XOAD_CUSTOM_HEADERS', $GLOBALS)) {

			foreach ($GLOBALS['_XOAD_CUSTOM_HEADERS'] as $fileName) {

				$returnValue .= '<script type="text/javascript" src="' . $base . ($optimized ? $fileName[1] : $fileName[0]) . '"></script>';
			}
		}

		if (array_key_exists('_XOAD_EXTENSION_HEADERS', $GLOBALS)) {

			foreach ($GLOBALS['_XOAD_EXTENSION_HEADERS'] as $extension => $files) {

				$extensionBase = $base . '/extensions/' . $extension . '/';

				foreach ($files as $fileName) {

					$returnValue .= '<script type="text/javascript" src="' . $extensionBase . ($optimized ? $fileName[1] : $fileName[0]) . '"></script>';
				}
			}
		}

		return $returnValue;
	}

	/**
	 * Registers XOAD Events header data.
	 *
	 * <p>You should call this method after {@link XOAD_Utilities::header}.</p>
	 * <p>XOAD Events header data includes server time and callback URL.</p>
	 *
	 * @access	public
	 *
	 * @param	string	$callbackUrl	XOAD Events callback URL.
	 *
	 * @return	string	HTML code to initialize XOAD Events.
	 *
	 * @static
	 *
	 */
	public static function eventsHeader($callbackUrl = null)
	{
		if ($callbackUrl == null) {

			$callbackUrl = XOAD_Utilities::getRequestUrl();
		}

		$returnValue = '<script type="text/javascript">';
		$returnValue .= 'xoad.events.callbackUrl = ' . XOAD_Client::register($callbackUrl) . ';';
		$returnValue .= 'xoad.events.lastRefresh = ' . XOAD_Client::register(XOAD_Utilities::getMicroTime()) . ';';
		$returnValue .= '</script>';

		return $returnValue;
	}

	/**
	 * Registers XOAD extension client header file.
	 *
	 * @access	public
	 *
	 * @param	string	$extension			The name of the XOAD extension.
	 *
	 * @param	string	$fileName			The extension JavaScript file name.
	 *										This file must be located in the
	 *										extension base folder.
	 *
	 * @param	string	$optimizedFileName	The optimized extension JavaScript file name.
	 *										This file must be located in the
	 *										extension base folder.
	 *
	 *
	 * @return	bool	true on success, false otherwise.
	 *
	 * @static
	 *
	 */
	public static function extensionHeader($extension, $fileName, $optimizedFileName = null)
	{
		if ( ! array_key_exists('_XOAD_EXTENSION_HEADERS', $GLOBALS)) {

			$GLOBALS['_XOAD_EXTENSION_HEADERS'] = array();
		}

		if ( ! array_key_exists('_XOAD_HEADERS', $GLOBALS)) {

			$GLOBALS['_XOAD_HEADERS'] = array();
		}

		$extension = strtolower($extension);

		if ( ! array_key_exists($extension, $GLOBALS['_XOAD_EXTENSION_HEADERS'])) {

			$GLOBALS['_XOAD_EXTENSION_HEADERS'][$extension] = array();
		}

		if (empty($optimizedFileName)) {

			$optimizedFileName = $fileName;
		}

		if (
		(in_array($fileName, $GLOBALS['_XOAD_HEADERS'])) &&
		(in_array($optimizedFileName, $GLOBALS['_XOAD_HEADERS']))) {

			return false;
		}

		$GLOBALS['_XOAD_EXTENSION_HEADERS'][$extension][] = array($fileName, $optimizedFileName);
		$GLOBALS['_XOAD_HEADERS'][] = $fileName;
		$GLOBALS['_XOAD_HEADERS'][] = $optimizedFileName;

		return true;
	}

	/**
	 * Registers custom client header file.
	 *
	 * @access	public
	 *
	 * @param	string	$fileName			The JavaScript file name.
	 *										This file must be located in the
	 *										base folder.
	 *
	 * @param	string	$optimizedFileName	The optimized JavaScript file name.
	 *										This file must be located in the
	 *										base folder.
	 *
	 *
	 * @return	bool	true on success, false otherwise.
	 *
	 * @static
	 *
	 */
	public static function customHeader($fileName, $optimizedFileName = null)
	{
		if ( ! array_key_exists('_XOAD_CUSTOM_HEADERS', $GLOBALS)) {

			$GLOBALS['_XOAD_CUSTOM_HEADERS'] = array();
		}

		if ( ! array_key_exists('_XOAD_HEADERS', $GLOBALS)) {

			$GLOBALS['_XOAD_HEADERS'] = array();
		}

		if (empty($optimizedFileName)) {

			$optimizedFileName = $fileName;
		}

		if (
		(in_array($fileName, $GLOBALS['_XOAD_HEADERS'])) &&
		(in_array($optimizedFileName, $GLOBALS['_XOAD_HEADERS']))) {

			return false;
		}

		$GLOBALS['_XOAD_CUSTOM_HEADERS'][] = array($fileName, $optimizedFileName);
		$GLOBALS['_XOAD_HEADERS'][] = $fileName;
		$GLOBALS['_XOAD_HEADERS'][] = $optimizedFileName;

		return true;
	}

	/**
	 * Returns the input string with all alphabetic characters
	 * converted to lower or upper case depending on the configuration.
	 *
	 * @param	string	$text	The text to convert to lower/upper case.
	 *
	 * @return	string	The converted text.
	 *
	 * @static
	 *
	 */
	public static function caseConvert($text)
	{
		return strtolower($text);
	}

	/**
	 * Adds a {@link XOAD_Utilities} events observer.
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
	public static function addObserver(&$observer, $className = 'XOAD_Utilities')
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
	public static function notifyObservers($event = 'default', $arg = null, $className = 'XOAD_Utilities')
	{
		return parent::notifyObservers($event, $arg, $className);
	}
}
?>