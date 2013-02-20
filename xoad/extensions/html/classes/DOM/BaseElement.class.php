<?php
/**
 * XOAD HTML DOM Base Element file.
 *
 * <p>This file defines the {@link XOAD_HTML_DOM_BaseElement} Class.</p>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_HTML
 *
 * @version		0.6.0.0
 *
 */

/**
 * XOAD HTML DOM Base Element Class.
 *
 * <p>This class is used as base class for all {@link XOAD_HTML}
 * DOM elements.</p>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_HTML
 *
 * @version		0.6.0.0
 *
 */
class XOAD_HTML_DOM_BaseElement
{
	/**
	 * Holds attributes collection. This array is not populated
	 * from the client and it is empty initially.
	 *
	 * @access	public
	 *
	 * @var		array
	 *
	 */
	public $attributes;

	/**
	 * Holds style collection. This array is not populated
	 * from the client and it is empty initially.
	 *
	 * @access	public
	 *
	 * @var		array
	 *
	 */
	public $style;

	/**
	 * Holds the keys that will be skipped (like id, clientCode ...).
	 *
	 * @access	protected
	 *
	 * @var		array
	 *
	 */
	public $skipKeys;

	/**
	 * Holds the client JavaScript code associated with the element(s).
	 *
	 * @access	protected
	 *
	 * @var		string
	 *
	 */
	public $clientCode;

	/**
	 * Creates a new instance of the {@link XOAD_HTML_DOM_BaseElement} class.
	 *
	 * @access	public
	 *
	 */
	public function __construct()
	{
		$this->attributes = array();

		$this->style = array();

		$this->skipKeys = array('clientCode');

		$this->clientCode = '';
	}

	/**
	 * This method removes an attribute from the element(s).
	 *
	 * <p>Example:</p>
	 * <code>
	 * $content =& XOAD_HTML::getElementById('content');
	 *
	 * $content->removeAttribute('disabled');
	 * </code>
	 *
	 * @access	public
	 *
	 * @param	string	$attName	String that names the attribute to be
	 *								removed from the element(s).
	 *
	 * @return	void
	 *
	 */
	public function removeAttribute($attName)
	{
		$this->clientCode .= $this->getElement() . '.removeAttribute(';
		$this->clientCode .= XOAD_Client::register($attName) . ');';
	}

	/**
	 * This method adds a new attribute or changes the
	 * value of an existing attribute on the element(s).
	 *
	 * <p>Example:</p>
	 * <code>
	 * $content =& XOAD_HTML::getElementById('content');
	 *
	 * $content->setAttribute('disabled', true);
	 * </code>
	 *
	 * @access	public
	 *
	 * @param	string	$attName	String that names the attribute to be
	 *								removed from the element(s).
	 *
	 * @return	void
	 *
	 */
	public function setAttribute($name, $value)
	{
		$this->clientCode .= $this->getElement() . '.setAttribute(';
		$this->clientCode .= XOAD_Client::register($name) . ',';
		$this->clientCode .= XOAD_Client::register($value) . ');';
	}

	/**
	 * Returns the JavaScript code of the DOM element.
	 *
	 * <p>You should not call this method directly.</p>
	 *
	 * @access	public
	 *
	 * @param	string	$element	The JavaScript element name.
	 *
	 * @return	string	JavaScript source code for the DOM element.
	 *
	 * @static
	 *
	 */
	public function process($element = null)
	{
		$returnValue = '';

		$objectVars = get_object_vars($this);

		foreach ($objectVars as $key => $value) {

			if ((strcasecmp($key, 'skipKeys') == 0) || (in_array($key, $this->skipKeys))) {

				continue;
			}

			if (strcasecmp($key, 'attributes') == 0) {

				foreach ($this->attributes as $key => $value) {

					if ($value === null) {

						$returnValue .= $element . '.removeAttribute("' . $key . '");';

					} else {

						$returnValue .= $element . '.setAttribute("' . $key . '", ' . XOAD_Client::register($value) . ');';
					}
				}

			} else if (strcasecmp($key, 'style') == 0) {

				foreach ($this->style as $key => $value) {

					$returnValue .= $element . '.style.' . $key . '=' . XOAD_Client::register($value) . ';';
				}

			} else {

				$assignField = $element . '.' . $key;

				$assignOperation = $assignField . '=' . XOAD_Client::register($value) . ';';

				if (strpos($assignOperation, XOAD_HTML_CURRENT_VALUE) !== false) {

					$assignOperation = str_replace(XOAD_HTML_CURRENT_VALUE, '"+' . $assignField .'+"', $assignOperation);
				}

				$returnValue .= $assignOperation;
			}
		}

		$returnValue .= $this->clientCode;

		return $returnValue;
	}
}
?>