<?php
/**
 * XOAD_Controls file.
 *
 * <p>This file defines the {@link XOAD_Controls} Class.</p>
 * <p>Example:</p>
 * <code>
 *
 * XOAD_Controls::register('xoad', array('Panel', 'PanelTitle', 'PanelContent'), 'xoad.controls.panel.js');
 *
 * </code>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Controls
 *
 * @version		0.6.0.0
 *
 */

/**
 * Holds a list with the controls that are already registered.
 */
$GLOBALS['_XOAD_CONTROLS_LIST'] = array();

/**
 * Holds a list with the scripts that are already included.
 */
$GLOBALS['_XOAD_CONTROLS_SCRIPT'] = array();

/**
 * XOAD_Controls Class.
 *
 * <p>This class allows you to register custom client controls.</p>
 * <p>Each control includes a JS file that defines the class that will handle
 * the control. Additionally, you can attach a server class and a HTML code.</p>
 * <p>Example:</p>
 * <code>
 *
 * XOAD_Controls::register('xoad', array('Panel', 'PanelTitle', 'PanelContent'), 'xoad.controls.panel.js');
 *
 * </code>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Controls
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Controls
{
	/**
	 * Gets the absolute path to the file.
	 *
	 * @access	private
	 *
	 * @return	string
	 *
	 */
	public static function getFileName($fileName)
	{
		if (strpos($fileName, DIRECTORY_SEPARATOR) === 0) {

			return $fileName;
		}

		if (strlen($fileName) >= 3) {

			if (
			($fileName{1} == ':') &&
			($fileName{2} == DIRECTORY_SEPARATOR)) {

				return $fileName;
			}
		}

		return XOAD_CONTROLS_BASE . '/library/' . $fileName;
	}

	/**
 	 * Registers a custom client control.
 	 *
	 * <p>Each control includes a JS file that defines the class that
	 * will handle the control. Additionally, you can attach a server class and
	 * a HTML code.</p>
	 *
	 * @access	public
	 *
	 * @param	string	$tagPrefix	The tag prefix for the control, required.
	 * @param	mixed	$tagName	The tag name for the control, required.
	 * @param	string	$jsFile		The relative path to the JS file that
	 * 								defines the class that will handle the
	 * 								control, required.
	 * @param	string	$phpFile	The relative/absolute path to the PHP file
	 * 								that defines the server class that is
	 * 								associated with the control, optional.
	 * @param	string	$url		The callback URL for the server class,
	 * 								optional.
	 * @param	string	$htmlFile	The relative/absolute path to the HTML file
	 * 								that defines the code that is associated
	 * 								with the control, optional.
	 *
	 * @return	string	HTML code to register the custom control.
	 *
	 * @static
	 *
	 */
	public static function register($tagPrefix = 'xoad', $tagName = null, $jsFile = null, $phpFile = null, $url = null, $htmlFile = null)
	{
		if (
		(empty($phpFile)) &&
		(empty($jsFile))) {

			return null;
		}

		if (XOAD_Utilities::getType($tagName) == 's_array') {

			$returnValue = '';

			foreach ($tagName as $name) {

				$returnValue .= XOAD_Controls::register($tagPrefix, $name, $jsFile, $phpFile, $url, $htmlFile);
			}

			return $returnValue;
		}

		if (XOAD_Utilities::getType($tagName) == 'a_array') {

			$returnValue = '';

			foreach ($tagName as $prefix => $name) {

				$returnValue .= XOAD_Controls::register($prefix, $name, $jsFile, $phpFile, $url, $htmlFile);
			}

			return $returnValue;
		}

		if (
		(empty($tagPrefix)) ||
		(empty($tagName))) {

			return null;
		}

		$registerAttribute = ($tagName == '@');

		$controlName = strtolower($tagPrefix) . ':' . strtolower($tagName);

		if (
		( ! $registerAttribute) &&
		(in_array($controlName, $GLOBALS['_XOAD_CONTROLS_LIST']))) {

			return null;
		}

		$phpControlName = $tagPrefix . '_Controls_' . $tagName;
		$jsControlName = $tagPrefix . '.controls.' . $tagName;

		$includeScript = '';
		$returnValue = '';

		if ( ! empty($phpFile)) {

			require_once(XOAD_Controls::getFileName($phpFile));

			if (empty($url)) {

				$url = XOAD_Utilities::getRequestUrl();
			}

			$phpObject = new $phpControlName();

			XOAD_Client::privateMethods($phpObject, array('getJSCode', 'getHtmlCode'));

			$returnValue .= XOAD_Client::register($phpControlName, $url) . ';';
		}

		if ( ! empty($jsFile)) {

			if ( ! in_array($jsFile, $GLOBALS['_XOAD_CONTROLS_SCRIPT'])) {

				$includeScript .= '<script type="text/javascript" src="' . htmlspecialchars($jsFile) . '"></script>';

				$GLOBALS['_XOAD_CONTROLS_SCRIPT'][] = $jsFile;
			}

		} else {

			if (
			(isset($phpObject)) &&
			(method_exists($phpObject, 'getJSCode'))) {

				$returnValue .= $phpObject->getJSCode($jsControlName, $phpControlName);
			}
		}

		$controlHtml = null;

		if ( ! empty($htmlFile)) {

			$controlHtml = @join(null, @file(XOAD_Controls::getFileName($htmlFile)));

		} else {

			if (
			(isset($phpObject)) &&
			(method_exists($phpObject, 'getHtmlCode'))) {

				$controlHtml = $phpObject->getHtmlCode($jsControlName, $phpControlName);
			}
		}

		if ( ! $registerAttribute) {

			$returnValue .= 'xoad.controls.list[' . sizeof($GLOBALS['_XOAD_CONTROLS_LIST']) . '] = {';
			$returnValue .= 'tagName:' . XOAD_Client::register($controlName);

			if ( ! empty($jsFile)) {

				$returnValue .= ',clientClass:' . XOAD_Client::register($jsControlName);
			}

			if ( ! empty($phpFile)) {

				$returnValue .= ',serverClass:' . XOAD_Client::register($phpControlName);
			}

			if ( ! empty($controlHtml)) {

				$returnValue .= ',html:' . XOAD_Client::register($controlHtml);
			}

			$returnValue .= '};';
		}

		if ( ! empty($returnValue)) {

			$returnValue = '<script type="text/javascript">' . $returnValue . '</script>';
		}

		$returnValue = $includeScript . $returnValue;

		$GLOBALS['_XOAD_CONTROLS_LIST'][] = $controlName;

		return $returnValue;
	}
}

?>