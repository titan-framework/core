<?php
/**
 * XOAD Extensions Configuration file.
 *
 * This file contains the list of the installed
 * extensions.
 *
 * @author	Stanimir Angeloff
 *
 * @package	XOAD
 *
 * @version	0.6.0.0
 *
 */

$xoadExtensions = array();

$xoadExtensions[] = 'html';
//$xoadExtensions[] = 'cache';
//$xoadExtensions[] = 'controls';

if ( ! defined('XOAD_CACHE_STORAGE_DSN')) {

	/**
	 * Defines the data source name and parameters to use
	 * when cache data is saved.
	 *
	 * <p>DSN Examples:</p>
	 * <code>
	 * File://c:\Cache
	 * MySQL://server=?;user=?;password=?;database=?;[port=?]
	 * PearDB://type=?;server=?;user=?;password=?;database=?;[port=?]
	 * </code>
	 *
	 */
	define('XOAD_CACHE_STORAGE_DSN', 'File://');
}

if ( ! defined('XOAD_CACHE_LIFETIME')) {

	/**
	 * Defines the default lifetime for the cache data.
	 *
	 * <p>The default value is 30 minutes. Keep in mind
	 * that you should use lower values for frequently
	 * updated data.</p>
	 */
	define('XOAD_CACHE_LIFETIME', 60 * 30);
}

?>