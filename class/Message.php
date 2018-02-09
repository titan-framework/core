<?php
/**
 * Launch messages to appear in interface.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage business
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance, Business, Action, Section
 */
class Message
{
	static private $message = FALSE;

	private $array = array ();

	private $cont = 0;

	const TEXT = 0;
	const HTML = 1;

	const SUCCESS = 'SUCCESS';
	const INFO = 'INFO';
	const ALERT = 'ALERT';
	const WARNING = 'WARNING';

	// Legacy
	const MESSAGE = self::SUCCESS;

	private final function __construct ()
	{
		$this->load ();
	}

	static public function singleton ()
	{
		if (self::$message !== FALSE)
			return self::$message;

		$class = __CLASS__;

		self::$message = new $class ();

		return self::$message;
	}

	public function save ()
	{
		$_SESSION['CACHE_MESSAGES'] = serialize ($this->array);
	}

	public function load ()
	{
		if (isset ($_SESSION['CACHE_MESSAGES']))
			$this->array = unserialize ($_SESSION['CACHE_MESSAGES']);
	}

	public function add ($type, $message)
	{
		if (trim ($message) != '' && in_array ($type, array (self::SUCCESS, self::INFO, self::ALERT, self::WARNING)))
			$this->array [] = array ($type, $message);
	}

	public function addMessage ($message)
	{
		if (trim ($message) != '')
			$this->array [] = array (self::SUCCESS, $message);
	}

	public function addWarning ($warning)
	{
		if (trim ($warning) != '')
			$this->array [] = array (self::WARNING, $warning);
	}

	public function get ($type = self::HTML)
	{
		if (!array_key_exists ($this->cont, $this->array))
			return NULL;

		$key = $this->cont++;

		if ($type == self::TEXT)
			return $this->array [$key][1];

		switch ($this->array [$key][0])
		{
			case self::SUCCESS:
				return '<div class="cMessageSuccess">'. $this->array [$key][1] .'</div>';

			case self::INFO:
				return '<div class="cMessageInfo">'. $this->array [$key][1] .'</div>';

			case self::WARNING:
				return '<div class="cMessageWarning">'. $this->array [$key][1] .'<a class="cReport" href="#" onclick="JavaScript: bugReport (\''. str_replace (array ("'", '"'), '', strip_tags ($this->array [$key][1])) .'\');">'. __ ('Technical issue?') .'</a></div>';

			case self::ALERT:
			default:
				return '<div class="cMessageAlert">'. $this->array [$key][1] .'</div>';
		}
	}

	public function has ()
	{
		return sizeof ($this->array);
	}

	public function clear ()
	{
		$this->array = array ();

		unset ($_SESSION['CACHE_MESSAGES']);
	}
}
