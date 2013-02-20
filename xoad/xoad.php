<?php
/**
 * XOAD all-in-one file.
 *
 * <p>This file includes all configuration files and
 * classes that the XOAD package contains.</p>
 * <p>The file also includes all installed
 * extensions.</p>
 *
 * @author	Stanimir Angeloff
 *
 * @package	XOAD
 *
 * @version	0.6.0.0
 *
 */

if ( ! defined('XOAD_BASE')) {

	/**
	 * XOAD base folder that contains all package files.
	 */
	define('XOAD_BASE', dirname(__FILE__));
}

/**
 * Loads the XOAD configuration file.
 */
require_once(XOAD_BASE . '/config/xoad.config.php');

/**
 * Loads the XOAD extensions configuration file.
 */
require_once(XOAD_BASE . '/config/extensions.config.php');

/**
 * Loads the file that defines the {@link XOAD_Observer} Class.
 */
require_once(XOAD_BASE . '/classes/Observer.class.php');

/**
 * Loads the class that is used to extend classes with events.
 */
require_once(XOAD_BASE . '/classes/Observable.class.php');

/**
 * Loads the class that defines extended functions that
 * the XOAD package uses and overrides some
 * deprecated functions, like gettype(...).
 */
require_once(XOAD_BASE . '/classes/Utilities.class.php');

/**
 * Loads the class that is used to serialize a PHP variable
 * into a {@link http://www.json.org JSON} string.
 */
require_once(XOAD_BASE . '/classes/Serializer.class.php');

/**
 * Loads the class that is used to register a PHP variable/class
 * in JavaScript.
 */
require_once(XOAD_BASE . '/classes/Client.class.php');

/**
 * Loads the class that is used as base class for all
 * XOAD Events storage providers.
 */
require_once(XOAD_BASE .'/classes/events/Storage.class.php');

/**
 * Loads the class that is used to handle client callbacks.
 */
require_once(XOAD_BASE . '/classes/Server.class.php');

if ( ! empty($xoadExtensions)) {

	foreach ($xoadExtensions as $extension) {

		/**
		 * XOAD extension base folder that contains all extension files.
		 */
		define('XOAD_' . strtoupper($extension) . '_BASE', XOAD_BASE . '/extensions/' . $extension);

		/**
		 * Loads the main extension file.
		 */
		require_once(XOAD_BASE . '/extensions/' . $extension . '/' . $extension . '.ext.php');
	}
}

if (defined('XOAD_AUTOHANDLE')) {

	if (XOAD_AUTOHANDLE) {

		XOAD_Server::runServer();

		if (defined('XOAD_CALLBACK')) {

			if (XOAD_CALLBACK) {

				exit;
			}
		}
	}
}
?>