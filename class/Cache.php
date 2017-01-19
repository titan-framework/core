<?php
/**
 * Implements Memcache of Zend Framework.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage util
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance
 * @link https://framework.zend.com/manual/1.12/en/zend.cache.backends.html#zend.cache.backends.memcached
 */
class Cache
{
	static private $self = FALSE;

	static private $cache = NULL;

	private final function __construct ()
	{
		$back = new Zend_Cache_Backend_Memcached (array (
			'servers' => array (array ('host' => '127.0.0.1', 'port' => '11211')),
			'compression' => TRUE));

		$log =  new Zend_Log ();

		$log->addWriter (new Zend_Log_Writer_Stream (Instance::singleton ()->getCachePath () .'memcache.log', 'a+'));

		$front = new Zend_Cache_Core (array (
			'caching' => TRUE,
			'cache_id_prefix' => Instance::singleton ()->getSession (),
			'logging' => TRUE,
			'logger'  => $log,
			'write_control' => TRUE,
			'automatic_serialization' => TRUE,
			'ignore_user_abort' => TRUE));

		self::$cache = Zend_Cache::factory ($front, $back);
	}

	static public function singleton ()
	{
		if (self::$self !== FALSE)
			return self::$self;

		$class = __CLASS__;

		self::$self = new $class ();

		return self::$self;
	}

	static public function isCached ($id)
	{
		self::singleton ();

		if (self::$cache === FALSE)
			return FALSE;

		return self::$cache->test ($id);
	}
}
