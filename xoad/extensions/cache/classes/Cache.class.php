<?php
/**
 * XOAD_Cache file.
 *
 * <p>This file defines the {@link XOAD_Cache} Class.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * XOAD_Cache::allowCaching(null, null, 30);
 *
 * ?>
 * </code>
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
 * Holds information about the classes and methods
 * that are allowed for caching.
 */
$GLOBALS['_XOAD_CACHE_DATA'] = array();

/**
 * XOAD_Cache Class.
 *
 * <p>This class allows you to cache the callbacks to
 * the server. By default the caching is disabled and
 * you must call {@link XOAD_Cache::allowCaching} to
 * configure which classes and methods will be cached.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * XOAD_Cache::allowCaching('ExampleClass', 'invokeMethod', 30);
 *
 * ?>
 * </code>
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
class XOAD_Cache
{
	/**
 	 * Retrieves an instanse to the configurated XOAD_Cache storage provider.
	 *
	 * @access	private
	 *
	 * @return	object	Singleton {@link XOAD_Cache_Storage} inherited class based
	 *					on the configuration (see {@link XOAD_CACHE_STORAGE_DSN}).
	 *
	 * @static
	 *
	 */
	public static function &getStorage()
	{
		static $instance;

		if ( ! isset($instance)) {

			$className = null;

			$classParameters = null;

			$separator = '://';

			$position = strpos(XOAD_CACHE_STORAGE_DSN, $separator);

			if ($position === false) {

				$className = XOAD_CACHE_STORAGE_DSN;

			} else {

				$className = substr(XOAD_CACHE_STORAGE_DSN, 0, $position);

				$classParameters = substr(XOAD_CACHE_STORAGE_DSN, $position + strlen($separator));
			}

			if (empty($className)) {

				return null;
			}

			$fileName = XOAD_CACHE_BASE . '/classes/storage/' . $className . '.class.php';

			/**
			 * Load the file that defines the {@link XOAD_Cache_Storage} class.
			 */
			require_once(XOAD_CACHE_BASE . '/classes/Storage.class.php');

			/**
			 * Load the file that defines the cache storage provider.
			 */
			require_once($fileName);

			$realClassName = 'XOAD_Cache_Storage_' . $className;

			if ( ! class_exists($realClassName)) {

				return null;
			}

			$instance = new $realClassName($classParameters);
		}

		return $instance;
	}

	/**
 	 * This method is called on every request to the server.
 	 *
 	 * <p>If the request matches the configurated criterias for
 	 * caching this method will call {@link XOAD_Cache::dispatch}.
	 *
	 * @access	public
	 *
	 * @param	array	$request	The data that is associated with the
	 *								callback.
	 *
	 * @return	bool	True if the request is cached, false otherwise.
	 *
	 * @static
	 *
	 */
	public static function initialize(&$request)
	{
		if ( ! empty($GLOBALS['_XOAD_CACHE_DATA'])) {

			foreach ($GLOBALS['_XOAD_CACHE_DATA'] as $cacheData) {

				$match = (
				(strcasecmp($request['className'], $cacheData['className']) == 0) ||
				($cacheData['className'] === null));

				$match &= (
				(strcasecmp($request['method'], $cacheData['method']) == 0) ||
				($cacheData['method'] === null));

				if ($match) {

					return XOAD_Cache::dispatch($request, $cacheData);
				}
			}
		}

		return false;
	}

	/**
 	 * This method is called when the request matches the configurated
 	 * criterias for caching.
	 *
	 * <p>If the request is cached and it's not expired then the cached
	 * response is used, otherwise the server dispatches the call
	 * and the response is cached.</p>
	 *
	 * @access	public
	 *
	 * @param	array	$request	The data that is associated with the
	 *								callback.
	 *
	 * @param	array	$cacheData	The data that is associated with the
	 *								caching criteria.
	 *
	 * @return	bool	True if the request is cached, false otherwise.
	 *
	 * @static
	 *
	 */
	public static function dispatch(&$request, &$cacheData)
	{
		$cacheId = 'c:';
		$cacheId .= strtolower($request['className']);
		$cacheId .= '__m:';
		$cacheId .= strtolower($request['method']);

		if ($cacheData['arguments'] !== null) {

			$argumentsList =& $cacheData['arguments'];

		} else {

			$argumentsList = array_keys($request['arguments']);
		}

		$cacheId .= '__a:';

		if ( ! empty($argumentsList)) {

			foreach ($argumentsList as $key) {

				if (array_key_exists($key, $request['arguments'])) {

					$cacheId .= serialize($request['arguments'][$key]);
				}
			}
		}

		$cacheId .= '__v:';

		if ($cacheData['members'] !== null) {

			$objectVars = array_map('strtolower', get_object_vars($request['source']));

			foreach ($cacheData['members'] as $key) {

				if (array_key_exists(strtolower($key), $objectVars)) {

					$cacheId .= serialize($request['source']->$key);
				}
			}
		}

		$storage =& XOAD_Cache::getStorage();

		$cacheId = $storage->generateID($cacheId);

		$storage->collectGarbage();

		$cachedResponse = $storage->load($cacheId);

		if ($cachedResponse != null) {

			print $cachedResponse;

			return true;

		} else {

			$GLOBALS['_XOAD_CACHE_ARGUMENTS'] = array(
			'id'	=>	$cacheId,
			'data'	=>	$cacheData
			);

			/**
			 * Defines a constant that is used to tell the server observer
			 * to call {@link XOAD_Cache::cacheRequest} when the request
			 * is dispatched.
			 */
			define('XOAD_CACHE_REQUEST', true);
		}

		return false;
	}

	/**
 	 * This method is called when the request matches the configurated
 	 * criterias for caching, but there is no data in the cache.
	 *
	 * @access	public
	 *
	 * @param	array	$request	The data that is associated with the
	 *								callback.
	 *
	 * @param	array	$response	The data that is associated with the
	 *								response.
	 *
	 * @return	bool	True if the request is cached, false otherwise.
	 *
	 * @static
	 *
	 */
	public static function cacheRequest(&$request, &$response)
	{
		if ( ! array_key_exists('_XOAD_CACHE_ARGUMENTS', $GLOBALS)) {

			return false;
		}

		$storage =& XOAD_Cache::getStorage();

		$cacheId =& $GLOBALS['_XOAD_CACHE_ARGUMENTS']['id'];
		$cacheData =& $GLOBALS['_XOAD_CACHE_ARGUMENTS']['data'];

		$cacheResponse = XOAD_Client::register($response);

		return $storage->save($cacheId, $cacheData['expire'], $cacheResponse);
	}

	/**
 	 * Installs a new caching criteria.
	 *
	 * <p>By default the caching is disabled and you must call this
	 * method to configure which classes and methods will be cached.</p>
	 * <p>If you call this method with no arguments, caching will be
	 * enabled for all classes and methods.</p>
	 * <p>When generating the ID for each request the class name, method name
	 * and arguments list is used to build a long string which is later
	 * used for the ID. You can configure {@link XOAD_Cache} to include
	 * some of the class variables too.</p>
	 * <p>Example:</p>
	 * <code>
	 * <?php
	 *
	 * // Allow caching for all classes.
	 * XOAD_Cache::allowCaching();
	 *
	 * // Allow caching only for the 'Example' class.
	 * XOAD_Cache::allowCaching('Example');
	 *
	 * // Allow caching only for the 'Example->invoke' method.
	 * XOAD_Cache::allowCaching('Example', 'invoke');
	 *
	 * // Allow caching for the 'invoke' method in every class.
	 * XOAD_Cache::allowCaching(null, 'invoke');
	 *
	 * ?>
	 * </code>
	 *
	 * @access	public
	 *
	 * @param	array	$className	The class name filter. This value can be null.
	 *
	 * @param	array	$method		The method name filter. This value can be null.
	 *
	 * @param	array	$expire		The lifetime time in seconds for the
	 *								cached data. This value can be null.
	 *
	 * @param	array	$arguments	Array that contains the list of arguments to
	 *								use when generating the request ID. By default
	 *								each argument is used. This value can be null.
	 *
	 * @param	array	$members	Array that contains the list of class variables
	 *								to use when generating the request ID. By
	 *								default no class variables are used. This value
	 *								can be null.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public function allowCaching($className = null, $method = null, $expire = null, $arguments = null, $members = null)
	{
		$GLOBALS['_XOAD_CACHE_DATA'][] = array(
		'className'	=>	$className,
		'method'	=>	$method,
		'expire'	=>	$expire,
		'arguments'	=>	$arguments,
		'members'	=>	$members
		);
	}
}

?>