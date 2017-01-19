<?php
/**
 * Help to get version and build numbers of Titan.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage util
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance
 */
class VersionHelper
{
	static private $version = FALSE;

	private final function __construct ()
	{
	}

	static public function singleton ()
	{
		if (self::$version !== FALSE)
			return self::$version;

		$class = __CLASS__;

		self::$version = new $class ();

		return self::$version;
	}
}
