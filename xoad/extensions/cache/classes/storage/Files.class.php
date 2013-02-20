<?php
/**
 * XOAD_Cache Storage Files Provider File.
 *
 * <p>This file defines the {@link XOAD_Cache_Storage_Files} Class.</p>
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
 * XOAD_Cache Storage Files Class.
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
class XOAD_Cache_Storage_Files extends XOAD_Cache_Storage
{
	/**
	 * Holds the directory where the cached data is saved.
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $container = 'cache';

	/**
	 * Creates a new instance of the {@link XOAD_Cache_Storage_Files} class.
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
	 * Gets the absolute path to the cache file.
	 *
	 * @access	private
	 *
	 * @param	string	$id	The ID of the cached data.
	 *
	 * @return	string
	 *
	 */
	public function getFileName($id)
	{
		if (strpos($this->container, DIRECTORY_SEPARATOR) === 0) {

			return $this->container . DIRECTORY_SEPARATOR . $id;
		}

		if (strlen($this->container) >= 3) {

			if (
			($this->container{1} == ':') &&
			($this->container{2} == DIRECTORY_SEPARATOR)) {

				return $this->container . DIRECTORY_SEPARATOR . $id;
			}
		}

		return XOAD_BASE . '/var/' . $this->container . '/' . $id;
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
		$directory = $this->getFileName('');

		clearstatcache();

		$handle = @dir($directory);

		if ( ! $handle) {

			return false;
		}

		$time = time();

		while (false !== ($fileName = $handle->read())) {

			if (
			($fileName == '.') ||
			($fileName == '..')) {

				continue;
			}

			$realFile = $directory . $fileName;

			if ( ! is_file($realFile)) {

				continue;
			}

			$fileHandle = @fopen($realFile, 'rb');

			if ( ! $fileHandle) {

				continue;
			}

			$expire = fread($fileHandle, 10);

			fclose($fileHandle);

			if ($expire < $time) {

				@unlink($realFile);
			}
		}

		$handle->close();

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
		$fileName = $this->getFileName($id);

		clearstatcache();

		if ( ! file_exists($fileName)) {

			return null;
		}

		$length = filesize($fileName);

		$handle = fopen($fileName, 'rb');

		if ( ! $handle) {

			return null;
		}

		flock($handle, LOCK_SH);

		$contents = fread($handle, $length);

		list($expire, $data) = explode("\t", $contents, 2);

		flock($handle, LOCK_UN);

		fclose($handle);

		if ($expire >= time()) {

			return $data;
		}

		return null;
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
		$fileName = $this->getFileName($id);

		if (empty($expires)) {

			$expires = XOAD_CACHE_LIFETIME;
		}

		$handle = @fopen($fileName, 'a+b');

		if ( ! $handle) {

			return false;
		}

		@ignore_user_abort(true);

		flock($handle, LOCK_EX);

		ftruncate($handle, 0);

		fwrite($handle, time() + $expires . "\t");
		fwrite($handle, $data);

		flock($handle, LOCK_UN);

		fclose($handle);

		@ignore_user_abort(false);

		return true;
	}
}
?>