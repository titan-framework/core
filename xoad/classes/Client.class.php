<?php
/**
 * XOAD Client file.
 *
 * <p>This file defines the {@link XOAD_Client} Class.</p>
 * <p>Example:</p>
 * <code>
 * <script type="text/javascript">
 * <?php
 *
 * class Calculator
 * {
 * 	var $result;
 *
 * 	function Calculator()
 * 	{
 * 		$this->result = 0;
 * 	}
 *
 * 	function Add($arg)
 * 	{
 * 		$this->result += $arg;
 * 	}
 * }
 *
 * require_once('xoad.php');
 *
 * print XOAD_Client::register('Calculator', 'server.php');
 *
 * ?>
 * </script>
 * </code>
 *
 * @author	Stanimir Angeloff
 *
 * @package	XOAD
 *
 * @version	0.6.0.0
 *
 */

/**
 * XOAD Client Class.
 *
 * <p>This class is used to register a PHP variable/class
 * in JavaScript.</p>
 * <p>This class is also used to assign meta data
 * to the classes. See
 * {@link XOAD_Client::publicMethods},
 * {@link XOAD_Client::privateMethods},
 * {@link XOAD_Client::publicVariables},
 * {@link XOAD_Client::privateVariables} and
 * {@link XOAD_Client::mapMethods} for more information.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * class Calculator
 * {
 * 	var $result;
 *
 * 	function Calculator()
 * 	{
 * 		$this->result = 0;
 * 	}
 *
 * 	function Add($arg)
 * 	{
 * 		$this->result += $arg;
 * 	}
 * }
 *
 * define('XOAD_AUTOHANDLE', true);
 *
 * require_once('xoad.php');
 *
 * ?>
 * <?= XOAD_Utilities::header() ?>
 * <script type="text/javascript">
 *
 * var calc = <?= XOAD_Client::register(new Calculator()) ?>;
 *
 * calc.add(10);
 * calc.add(20);
 *
 * alert(calc.result);
 *
 * </script>
 * </code>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Client extends XOAD_Observable
{
	/**
	 * Registers a PHP variable/class in JavaScript.
	 *
	 * <p>Example:</p>
	 * <code>
	 * <script type="text/javascript">
	 * <?php require_once('xoad.php'); ?>
	 *
	 * var arr = <?= XOAD_Client::register(array(1, 2, "string", array("Nested"))) ?>;
	 *
	 * alert(arr);
	 *
	 * </script>
	 * </code>
	 *
	 * @access	public
	 *
	 * @param	mixed	$var	Variable/Class name to register.
	 *
	 * @param	mixed	$params	When registering a variable/class you can
	 *							provide extended parameters, like class name
	 *							and callback URL.
	 *
	 * @return	string	JavaString code that represents the variable/class.
	 *
	 * @static
	 *
	 */
	public static function register($var, $params = null)
	{
		$type = XOAD_Utilities::getType($var);

		if ($type == 'object') {

			$paramsType = XOAD_Utilities::getType($params);

			if ($paramsType != 'string') {

				$callbackUrl = XOAD_Utilities::getRequestUrl();

				if ($paramsType == 'a_array') {

					if ( ! empty($params['class'])) {

						$className = $params['class'];
					}

					if ( ! empty($params['url'])) {

						$callbackUrl = $params['url'];
					}
				}

			} else {

				$callbackUrl = $params;
			}

			if (method_exists($var, XOAD_CLIENT_METADATA_METHOD_NAME)) {

				call_user_func_array(array(&$var, XOAD_CLIENT_METADATA_METHOD_NAME), array ());
			}

			$objectCode = array();

			if (empty($className)) {

				$className = XOAD_Utilities::caseConvert(get_class($var));
			}

			$meta = get_object_vars($var);

			$objectMeta = null;

			if (isset($meta['xoadMeta'])) {

				if (XOAD_Utilities::getType($meta['xoadMeta']) == 'object') {

					if (strcasecmp(get_class($meta['xoadMeta']), 'XOAD_Meta') == 0) {

						$objectMeta = $meta['xoadMeta'];

						unset($meta['xoadMeta']);

						unset($var->xoadMeta);
					}
				}
			}

			if (sizeof($meta) > 0) {

				$attachMeta = array();

				foreach ($meta as $key => $value) {

					if ( ! empty($objectMeta)) {

						if ( ! $objectMeta->isPublicVariable($key)) {

							unset($meta[$key]);

							unset($var->$key);

							continue;
						}
					}

					$valueType = XOAD_Utilities::getType($value);

					if (
					($valueType == 'object') ||
					($valueType == 's_array') ||
					($valueType == 'a_array')) {

						$var->$key = XOAD_SERIALIZER_SKIP_STRING . XOAD_Client::register($var->$key, $callbackUrl);
					}

					$attachMeta[$key] = $valueType;
				}

				$var->__meta = $attachMeta;

				$var->__size = sizeof($attachMeta);

			} else {

				$var->__meta = null;

				$var->__size = 0;
			}

			$var->__class = $className;

			$var->__url = $callbackUrl;

			$var->__uid = md5(uniqid(rand(), true));

			$var->__output = null;

			$var->__timeout = null;

			$serialized = XOAD_Serializer::serialize($var);

			$objectCode[] = substr($serialized, 1, strlen($serialized) - 2);

			$objectCode[] = '"__clone":function(obj){xoad.clone(this,obj)}';

			$objectCode[] = '"__serialize":function(){return xoad.serialize(this)}';

			$objectCode[] = '"catchEvent":function(){return xoad.catchEvent(this,arguments)}';

			$objectCode[] = '"ignoreEvent":function(){return xoad.ignoreEvent(this,arguments)}';

			$objectCode[] = '"postEvent":function(){return xoad.postEvent(this,arguments)}';

			$objectCode[] = '"fetchOutput":function(){return this.__output}';

			$objectCode[] = '"setTimeout":function(miliseconds){this.__timeout=miliseconds}';

			$objectCode[] = '"getTimeout":function(){return this.__timeout}';

			$objectCode[] = '"clearTimeout":function(){this.__timeout=null}';

			$classMethods = get_class_methods($var);

			for ($iterator = sizeof($classMethods) - 1; $iterator >= 0; $iterator --) {

				if (strcasecmp($className, $classMethods[$iterator]) == 0) {

					unset($classMethods[$iterator]);

					continue;
				}

				if (strcasecmp($classMethods[$iterator], XOAD_CLIENT_METADATA_METHOD_NAME) == 0) {

					unset($classMethods[$iterator]);

					continue;
				}

				if ( ! empty($objectMeta)) {

					if ( ! $objectMeta->isPublicMethod($classMethods[$iterator])) {

						unset($classMethods[$iterator]);

						continue;
					}
				}
			}

			if (sizeof($classMethods) > 0) {

				$index = 0;

				$length = sizeof($classMethods);

				$returnValue = '';

				foreach ($classMethods as $method) {

					$methodName = XOAD_Utilities::caseConvert($method);

					if ( ! empty($objectMeta)) {

						$mapMethodName = $objectMeta->findMethodName($methodName);

						if (strcmp($mapMethodName, $methodName) != 0) {

							$methodName = $mapMethodName;
						}
					}

					$serialized = XOAD_Serializer::serialize($methodName);

					$returnValue .= $serialized;

					$returnValue .= ':';

					$returnValue .= 'function(){return xoad.call(this,' . $serialized .',arguments)}';

					if ($index < $length - 1) {

						$returnValue .= ',';
					}

					$index ++;
				}

				$objectCode[] = $returnValue;
			}

			$returnValue = '{' . join(',', $objectCode) . '}';

			return $returnValue;

		} else if (($type == 's_array') || ($type == 'a_array')) {

			foreach ($var as $key => $value) {

				$valueType = XOAD_Utilities::getType($value);

				if (
				($valueType == 'object') ||
				($valueType == 's_array') ||
				($valueType == 'a_array')) {

					$var[$key] = XOAD_SERIALIZER_SKIP_STRING . XOAD_Client::register($var[$key], $params);
				}
			}

		} else if ($type == 'string') {

			$paramsType = XOAD_Utilities::getType($params);

			if ($paramsType == 'string') {

				if (class_exists($var)) {

					$classObject = new $var;

					$classCode = XOAD_Client::register($classObject, array('class' => $var, 'url' => $params));

					$classCode = $var . '=function(){return ' . $classCode . '}';

					return $classCode;
				}
			}
		}

		return XOAD_Serializer::serialize($var);
	}

	/**
	 * Assigns public methods to the class meta data.
	 *
	 * @param	object	$var		The object where the meta data is stored.
	 *
	 * @param	array	$methods	The class public methods.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function publicMethods(&$var, $methods)
	{
		if (XOAD_Utilities::getType($var) != 'object') {

			return false;
		}

		if ( ! isset($var->xoadMeta)) {

			require_once(XOAD_BASE . '/classes/Meta.class.php');

			$var->xoadMeta = new XOAD_Meta();
		}

		$var->xoadMeta->setPublicMethods($methods);
		
		return true;
	}

	/**
	 * Assigns private methods to the class meta data.
	 *
	 * @param	object	$var		The object where the meta data is stored.
	 *
	 * @param	array	$methods	The class private methods.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function privateMethods(&$var, $methods)
	{
		if (XOAD_Utilities::getType($var) != 'object') {

			return false;
		}

		if ( ! isset($var->xoadMeta)) {

			require_once(XOAD_BASE . '/classes/Meta.class.php');

			$var->xoadMeta = new XOAD_Meta();
		}

		$var->xoadMeta->setPrivateMethods($methods);
		
		return true;
	}

	/**
	 * Assigns public variables to the class meta data.
	 *
	 * @param	object	$var		The object where the meta data is stored.
	 *
	 * @param	array	$variables	The class public variables.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function publicVariables(&$var, $variables)
	{
		if (XOAD_Utilities::getType($var) != 'object') {

			return false;
		}

		if ( ! isset($var->xoadMeta)) {

			require_once(XOAD_BASE . '/classes/Meta.class.php');

			$var->xoadMeta = new XOAD_Meta();
		}

		$var->xoadMeta->setPublicVariables($variables);
		
		return true;
	}

	/**
	 * Assigns private variables to the class meta data.
	 *
	 * @param	object	$var		The object where the meta data is stored.
	 *
	 * @param	array	$variables	The class private variables.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function privateVariables(&$var, $variables)
	{
		if (XOAD_Utilities::getType($var) != 'object') {

			return false;
		}

		if ( ! isset($var->xoadMeta)) {

			require_once(XOAD_BASE . '/classes/Meta.class.php');

			$var->xoadMeta = new XOAD_Meta();
		}

		$var->xoadMeta->setPrivateVariables($variables);
		
		return true;
	}

	/**
	 * Assigns methods map to the class meta data.
	 *
	 * @param	object	$var		The object where the meta data is stored.
	 *
	 * @param	array	$methodsMap	The class methods map.
	 *
	 * @return	void
	 *
	 * @static
	 *
	 */
	public static function mapMethods(&$var, $methodsMap)
	{
		if (XOAD_Utilities::getType($var) != 'object') {

			return false;
		}

		if ( ! isset($var->xoadMeta)) {

			require_once(XOAD_BASE . '/classes/Meta.class.php');

			$var->xoadMeta = new XOAD_Meta();
		}

		$var->xoadMeta->setMethodsMap($methodsMap);
		
		return true;
	}

	/**
	 * Adds a {@link XOAD_Client} events observer.
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
	public static function addObserver(&$observer, $className = 'XOAD_Client')
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
	public static function notifyObservers($event = 'default', $arg = null, $className = 'XOAD_Client')
	{
		return parent::notifyObservers($event, $arg, $className);
	}
}
?>