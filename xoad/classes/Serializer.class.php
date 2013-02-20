<?php
/**
 * XOAD Serializer file.
 *
 * <p>This file defines the {@link XOAD_Serializer} Class.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * $arr = array(1, 2, "String", array("Nested", 3, 4, array(5, 6)));
 *
 * ?>
 * <script type="text/javascript">
 *
 * var arr = <?= XOAD_Serializer::serialize($arr) ?>;
 *
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
 * XOAD Serializer Class.
 *
 * <p>This class is used to serialize a PHP variable
 * into a {@link http://www.json.org JSON} string.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * $arr = array(1, 2, "String", array("Nested", 3, 4, array(5, 6)));
 *
 * ?>
 * <script type="text/javascript">
 *
 * var arr = <?= XOAD_Serializer::serialize($arr) ?>;
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
class XOAD_Serializer extends XOAD_Observable
{
	/**
	 * Serializes a PHP variable into a {@link http://www.json.org JSON} string.
	 *
	 * <p>Example:</p>
	 * <code>
	 * <script type="text/javascript">
	 * <?php require_once('xoad.php'); ?>
	 *
	 * var arr = <?= XOAD_Serializer::serialize(array(1, 2, "string", array("Nested"))) ?>;
	 *
	 * alert(arr);
	 *
	 * </script>
	 * </code>
	 *
	 * @access	public
	 *
	 * @param	mixed	$var	Variable to serialize.
	 *
	 * @return	string	{@link http://www.json.org JSON} string that
	 *					represents the variable.
	 *
	 * @static
	 *
	 */
	public static function serialize(&$var)
	{
		$type = XOAD_Utilities::getType($var);

		if ($type == 'bool') {

			if ($var) {

				return "true";

			} else {

				return "false";
			}

		} else if ($type == 'int') {

			return sprintf('%d', $var);

		} else if ($type == 'float') {

			return sprintf('%f', $var);

		} else if ($type == 'string') {

			if (strlen($var) >= strlen(XOAD_SERIALIZER_SKIP_STRING)) {

				if (strcasecmp(substr($var, 0, strlen(XOAD_SERIALIZER_SKIP_STRING)), XOAD_SERIALIZER_SKIP_STRING) == 0) {

					return substr($var, strlen(XOAD_SERIALIZER_SKIP_STRING), strlen($var) - strlen(XOAD_SERIALIZER_SKIP_STRING));
				}
			}

			// This code is based on morris_hirsch's
			// comment in utf8_decode function documentation.
			//
			// http://bg.php.net/utf8_decode
			//
			// Thank you.
			//

			$ascii = '';

			$length = strlen($var);

			for ($iterator = 0; $iterator < $length; $iterator ++) {

				$char = $var{$iterator};

				$charCode = ord($char);

				if ($charCode == 0x08) {

					$ascii .= '\b';

				} else if ($charCode == 0x09) {

					$ascii .= '\t';

				} else if ($charCode == 0x0A) {

					$ascii .= '\n';

				} else if ($charCode == 0x0C) {

					$ascii .= '\f';

				} else if ($charCode == 0x0D) {

					$ascii .= '\r';

				} else if (($charCode == 0x22) || ($charCode == 0x2F) || ($charCode == 0x5C)) {

					$ascii .= '\\' . $var{$iterator};

				} else if ($charCode < 128) {

					$ascii .= $char;

				} else if ($charCode >> 5 == 6) {

					$byteOne = ($charCode & 31);

					$iterator ++;

					$char = $var{$iterator};

					$charCode = ord($char);

					$byteTwo = ($charCode & 63);

					$charCode = ($byteOne * 64) + $byteTwo;

					$ascii .= sprintf('\u%04s', dechex($charCode));

				} else if ($charCode >> 4 == 14) {

					$byteOne = ($charCode & 31);

					$iterator ++;

					$char = $var{$iterator};

					$charCode = ord($char);

					$byteTwo = ($charCode & 63);

					$iterator ++;

					$char = $var{$iterator};

					$charCode = ord($char);

					$byteThree = ($charCode & 63);

					$charCode = ((($byteOne * 64) + $byteTwo) * 64) + $byteThree;

					$ascii .= sprintf('\u%04s', dechex($charCode));

				} else if ($charCode >> 3 == 30) {

					$byteOne = ($charCode & 31);

					$iterator ++;

					$char = $var{$iterator};

					$charCode = ord($char);

					$byteTwo = ($charCode & 63);

					$iterator ++;

					$char = $var{$iterator};

					$charCode = ord($char);

					$byteThree = ($charCode & 63);

					$iterator ++;

					$char = $var{$iterator};

					$charCode = ord($char);

					$byteFour = ($charCode & 63);

					$charCode = ((((($byteOne * 64) + $byteTwo) * 64) + $byteThree) * 64) + $byteFour;

					$ascii .= sprintf('\u%04s', dechex($charCode));
				}
			}

			return ('"' . $ascii . '"');

		} else if ($type == 's_array') {

			$index = 0;

			$length = sizeof($var);

			$returnValue = '[';

			foreach ($var as $value) {

				$returnValue .= XOAD_Serializer::serialize($value);

				if ($index < $length - 1) {

					$returnValue .= ',';
				}

				$index ++;
			}

			$returnValue .= ']';

			return $returnValue;

		} else if ($type == 'a_array') {

			$index = 0;

			$length = sizeof($var);

			$returnValue = '{';

			foreach ($var as $key => $value) {

				$returnValue .= XOAD_Serializer::serialize($key);

				$returnValue .= ':';

				$returnValue .= XOAD_Serializer::serialize($value);

				if ($index < $length - 1) {

					$returnValue .= ',';
				}

				$index ++;
			}

			$returnValue .= '}';

			return $returnValue;

		} else if ($type == 'object') {

			$objectVars = get_object_vars($var);

			return XOAD_Serializer::serialize($objectVars);
		}

		return "null";
	}

	/**
	 * Adds a {@link XOAD_Serializer} events observer.
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
	public static function addObserver(&$observer, $className = 'XOAD_Serializer')
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
	public static function notifyObservers($event = 'default', $arg = null, $className = 'XOAD_Serializer')
	{
		return parent::notifyObservers($event, $arg, $className);
	}
}
?>