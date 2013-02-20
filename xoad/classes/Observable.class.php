<?php
/**
 * XOAD Observable file.
 *
 * <p>This file defines the {@link XOAD_Observable} Class.</p>
 * <p>This class is used internally only.</p>
 *
 * @author	Stanimir Angeloff
 *
 * @package	XOAD
 *
 * @version	0.6.0.0
 *
 */

/**
 * XOAD Observable Class.
 *
 * <p>This class is used to extend classes with events.</p>
 * <p>You should never use this class directly. Rather,
 * use the classes that extend this class.</p>
 *
 * @access		public
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Observable
{
	/**
	 *
	 * @access	public
	 *
	 * @return	bool
	 *
	 */
	public static function addObserver(&$observer, $className = 'XOAD_Observable')
	{
		if (XOAD_Utilities::getType($observer) != 'object') {

			return false;
		}

		if ( ! is_subclass_of($observer, 'XOAD_Observer')) {

			return false;
		}

		if ( ! isset($GLOBALS['_XOAD_OBSERVERS'])) {

			$GLOBALS['_XOAD_OBSERVERS'] = array();
		}

		$globalObservers =& $GLOBALS['_XOAD_OBSERVERS'];

		$className = strtolower($className);

		if ( ! isset($globalObservers[$className])) {

			$globalObservers[$className] = array();
		}

		$globalObservers[$className][] =& $observer;

		return true;
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	bool
	 *
	 */
	public static function notifyObservers($event = 'default', $arg = null, $className = 'XOAD_Observable')
	{
		if (empty($GLOBALS['_XOAD_OBSERVERS'])) {

			return true;
		}

		$globalObservers =& $GLOBALS['_XOAD_OBSERVERS'];

		$className = strtolower($className);

		if (empty($globalObservers[$className])) {

			return true;
		}

		$returnValue = true;

		foreach ($globalObservers[$className] as $observer) {

			$eventValue = $observer->updateObserver($event, $arg);

			if (XOAD_Utilities::getType($eventValue) == 'bool') {

				$returnValue &= $eventValue;
			}
		}

		return $returnValue;
	}
}

?>