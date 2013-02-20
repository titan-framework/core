<?php
/**
 * XOAD_Cache Storage File.
 *
 * <p>This file defines the {@link XOAD_Cache_Storage} Class.</p>
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
 * XOAD_Cache Storage Class.
 *
 * <p>This class is used as base class for all XOAD_Cache
 * storage providers.</p>
 * <p>Example XOAD_Cache provider: {@link XOAD_Cache_Storage_Files}.</p>
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
class XOAD_Cache_Storage
{
	/**
	 * Creates a new instance of the {@link XOAD_Cache_Storage} class.
	 *
	 * @access	public
	 *
	 * @param	string	$dsn	The data source name and parameters to use
	 *							when creating the instance.
	 *
 	 */
	public function __construct($dsn = null)
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
 	 * Generates an unique ID from the given data.
	 *
	 * @access	public
	 *
	 * @param	string	$data	The data to use when generating the ID.
	 *
	 * @return	string	The unique ID from the given data.
	 *
	 */
	public function generateID($data = null)
	{
		return md5($data);
	}

	/**
	 * Abstract base class method.
	 *
	 * <p>Successor classes should override this method.</p>
	 *
	 * @access	public
	 *
	 * @return	bool
	 *
	 */
	public function collectGarbage()
	{
		return true;
	}

	/**
	 * Abstract base class method.
	 *
	 * <p>Successor classes should override this method.</p>
	 *
	 * @param	mixed	$id	The ID of the cached data.
	 *
	 * @access	public
	 *
	 * @return	mixed
	 *
	 */
	public function load($id = null)
	{
		return null;
	}

	/**
	 * Abstract base class method.
	 *
	 * <p>Successor classes should override this method.</p>
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
	 * @return	bool
	 *
	 */
	public function save($id = null, $expires = null, $data = null)
	{
		return true;
	}
}
?>