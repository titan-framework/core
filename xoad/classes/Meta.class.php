<?php
/**
 * XOAD Meta file.
 *
 * <p>This file defines the {@link XOAD_Meta} Class.</p>
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
 * XOAD Meta Class.
 *
 * <p>This class is used to extend classes with meta
 * data, such as private methods and/or variables.</p>
 * <p>You should never use this class directly.
 * Rather, use the {@link XOAD_Utilities} class.</p>
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
class XOAD_Meta extends XOAD_Observable
{
	/**
	 *
	 * @access	private
	 *
	 * @var		array
	 *
	 */
	public $publicMethods;

	/**
	 *
	 * @access	private
	 *
	 * @var		array
	 *
	 */
	public $privateMethods;

	/**
	 *
	 * @access	private
	 *
	 * @var		array
	 *
	 */
	public $publicVariables;

	/**
	 *
	 * @access	private
	 *
	 * @var		array
	 *
	 */
	public $privateVariables;

	/**
	 *
	 * @access	private
	 *
	 * @var		array
	 *
	 */
	public $methodsMap;

	/**
	 *
	 * @access	public
	 *
	 * @return	void
	 *
	 */
	public function setPublicMethods($methods)
	{
		$methodsType = XOAD_Utilities::getType($methods);

		if ($methodsType == 'string') {

			$this->publicMethods = array(XOAD_Utilities::caseConvert($methods));

		} else if (($methodsType == 's_array') || ($methodsType == 'a_array')) {

			$this->publicMethods = array_map(array('XOAD_Utilities', 'caseConvert'), $methods);

		} else {

			$this->publicMethods = null;
		}
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	void
	 *
	 */
	public function setPrivateMethods($methods)
	{
		$methodsType = XOAD_Utilities::getType($methods);

		if ($methodsType == 'string') {

			$this->privateMethods = array(XOAD_Utilities::caseConvert($methods));

		} else if (($methodsType == 's_array') || ($methodsType == 'a_array')) {

			$this->privateMethods = array_map(array('XOAD_Utilities', 'caseConvert'), $methods);

		} else {

			$this->privateMethods = null;
		}
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	void
	 *
	 */
	public function setPublicVariables($variables)
	{
		$variablesType = XOAD_Utilities::getType($variables);

		if ($variablesType == 'string') {

			$this->publicVariables = array(XOAD_Utilities::caseConvert($variables));

		} else if (($variablesType == 's_array') || ($variablesType == 'a_array')) {

			$this->publicVariables = array_map(array('XOAD_Utilities', 'caseConvert'), $variables);

		} else {

			$this->publicVariables = null;
		}
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	void
	 *
	 */
	public function setPrivateVariables($variables)
	{
		$variablesType = XOAD_Utilities::getType($variables);

		if ($variablesType == 'string') {

			$this->privateVariables = array(XOAD_Utilities::caseConvert($variables));

		} else if (($variablesType == 's_array') || ($variablesType == 'a_array')) {

			$this->privateVariables = array_map(array('XOAD_Utilities', 'caseConvert'), $variables);

		} else {

			$this->privateVariables = null;
		}
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	void
	 *
	 */
	public function setMethodsMap($methodsMap)
	{
		$methodsMapType = XOAD_Utilities::getType($methodsMap);

		if ($methodsMapType == 'string') {

			$this->methodsMap = array(XOAD_Utilities::caseConvert($methodsMap) => $methodsMap);

		} else if (($methodsMapType == 's_array') || ($methodsMapType == 'a_array')) {

			$map = array();

			foreach ($methodsMap as $method) {

				$map[XOAD_Utilities::caseConvert($method)] = $method;
			}

			$this->methodsMap = $map;

		} else {

			$this->methodsMap = null;
		}
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	bool
	 *
	 */
	public function isPublicMethod($methodName)
	{
		if ( ! empty($this->privateMethods)) {

			if (in_array(XOAD_Utilities::caseConvert($methodName), $this->privateMethods)) {

				return false;
			}
		}

		if ( ! empty($this->publicMethods)) {

			if ( ! in_array(XOAD_Utilities::caseConvert($methodName), $this->publicMethods)) {

				return false;
			}
		}

		return true;
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	bool
	 *
	 */
	public function isPublicVariable($variableName)
	{
		if ( ! empty($this->privateVariables)) {

			if (in_array(XOAD_Utilities::caseConvert($variableName), $this->privateVariables)) {

				return false;
			}
		}

		if ( ! empty($this->publicVariables)) {

			if ( ! in_array(XOAD_Utilities::caseConvert($variableName), $this->publicVariables)) {

				return false;
			}
		}

		return true;
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	string
	 *
	 */
	public function findMethodName($methodName)
	{
		if ( ! empty($this->methodsMap)) {

			$name = XOAD_Utilities::caseConvert($methodName);

			if (isset($this->methodsMap[$name])) {

				return $this->methodsMap[$name];
			}
		}

		return $methodName;
	}

	/**
	 * Adds a {@link XOAD_Meta} events observer.
	 *
	 * @access	public
	 *
	 * @param	mixed	$observer	The observer object to add (must extend {@link XOAD_Observer}).
	 *
	 * @return	string	true on success, false otherwise.
	 *
	 * @static
	 *
	 */
	public static function addObserver(&$observer, $className = 'XOAD_Meta')
	{
		return parent::addObserver($observer, $className);
	}

	/**
	 *
	 * @access	public
	 *
	 * @return	bool
	 *
	 */
	public static function notifyObservers($event = 'default', $arg = null, $className = 'XOAD_Meta')
	{
		return parent::notifyObservers($event, $arg, $className);
	}
}
?>