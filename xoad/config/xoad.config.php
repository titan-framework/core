<?php
/**
 * XOAD Configuration file.
 *
 * <p>This file contains most of the available XOAD
 * configuration options.</p>
 * <p>You can modify this file, but you should be aware
 * that XOAD is only tested with the default
 * configuration.</p>
 *
 * @author	Stanimir Angeloff
 *
 * @package	XOAD
 *
 * @version	0.6.0.0
 *
 */

if ( ! defined('XOAD_SERIALIZER_SKIP_STRING')) {

	/**
	 * Defines the prefix that is used to indicate the
	 * Serializer to skip string serialization.
	 *
	 * <p>Example:</p>
	 * <code>
	 * <?php
	 *
	 * require_once('xoad.php');
	 *
	 * $arr = array(1, 2, XOAD_SERIALIZER_SKIP_STRING . 'function skip() { alert("skip."); }');
	 *
	 * ?>
	 * <script type="text/javascript">
	 *
	 * var arr = <?= XOAD_Serializer::serialize($arr) ?>;
	 *
	 * arr[2]();
	 *
	 * </script>
	 * </code>
	 */
	define('XOAD_SERIALIZER_SKIP_STRING', '<![xoadSerializer:skipString[-a238fb10DC7-[');
}

if ( ! defined('XOAD_CLIENT_METADATA_METHOD_NAME')) {

	/**
	 * Defines the method name that is called when XOAD
	 * needs more information about an object.
	 *
	 * <p>Every class that you will register with {@link XOAD_Client}
	 * should implement this method to provide more information
	 * about its methods and variables.</p>
	 * <p>Example:</p>
	 * <code>
	 * <script type="text/javascript">
	 * <?php
	 *
	 * class MetaExample
	 * {
	 * 	var $privateVar;
	 * 	var $publicVar;
	 *
	 * 	function PrivateMethod() {}
	 * 	function PublicMethod() {}
	 *
	 * 	function xoadGetMeta()
	 * 	{
	 * 		XOAD_Client::privateMethods($this, array('PrivateMethod'));
	 *
	 * 		XOAD_Client::privateVariables($this, array('privateVar'));
	 *
	 * 		XOAD_Client::mapMethods($this, array('PublicMethod'));
	 * 	}
	 * }
	 *
	 * require_once('xoad.php');
	 *
	 * print XOAD_Client::register('MetaExample', 'server.php');
	 *
	 * ?>
	 * </script>
	 * </code>
	 *
	 */
	define('XOAD_CLIENT_METADATA_METHOD_NAME', 'xoadGetMeta');
}

if ( ! defined('XOAD_EVENTS_STORAGE_DSN')) {

	/**
	 * Defines the data source name and parameters to use
	 * when event's information is saved.
	 *
	 * <p>DSN Examples:</p>
	 * <code>
	 * File://c:\events.txt
	 * MySQL://server=?;user=?;password=?;database=?;[port=?]
	 * PearDB://type=?;server=?;user=?;password=?;database=?;[port=?]
	 * </code>
	 *
	 */
	define('XOAD_EVENTS_STORAGE_DSN', 'File://');
}

if ( ! defined('XOAD_EVENTS_LIFETIME')) {

	/**
	 * Defines the default lifetime for an event.
	 *
	 * <p>The default value is 2 minutes. Please note, that
	 * the lifetime should be between 30 seconds and 5 minutes
	 * for performance reasons.</p>
	 */
	define('XOAD_EVENTS_LIFETIME', 60 * 2);
}

?>