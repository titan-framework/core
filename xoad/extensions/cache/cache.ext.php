<?php
/**
 * XOAD_Cache Extension File.
 *
 * <p>This file initialized the XOAD_Cache extension
 * and installs all necessary server observers.</p>
 * <p>Note that this file is not included directly.
 * You should add the extension manually to the
 * extensions configuration file.</p>
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
 * Loads the file that defines the {@link XOAD_Cache} class.
 */
require_once(XOAD_CACHE_BASE . '/classes/Cache.class.php');

/**
 * Load the file that defines the XOAD_Cache Server observer.
 */
require_once(XOAD_CACHE_BASE . '/classes/ServerObserver.class.php');

XOAD_Server::addObserver(new XOAD_Cache_ServerObserver());

?>