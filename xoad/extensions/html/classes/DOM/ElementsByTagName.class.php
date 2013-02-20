<?php
/**
 * XOAD HTML DOM Elements By Tag Name file.
 *
 * <p>This file defines the {@link XOAD_HTML_DOM_ElementsByTagName} Class.</p>
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
 * Loads the file that defines the base class for {@link XOAD_HTML_DOM_ElementsByTagName}.
 */
require_once(XOAD_HTML_BASE . '/classes/DOM/BaseElement.class.php');

/**
 * XOAD HTML DOM Elements By Tag Name Class.
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
class XOAD_HTML_DOM_ElementsByTagName extends XOAD_HTML_DOM_BaseElement
{
	/**
	 * Creates a new instance of the {@link XOAD_HTML_DOM_ElementsByTagName} class.
	 *
	 * @param	string	$tagName	String that holds the tag name of the elements.
	 *
	 * @access	public
	 *
	 */
	public function __construct($tagName)
	{
		parent::__construct();

		$this->tagName = $tagName;

		$this->skipKeys[] = 'tagName';
	}

	/**
	 * Returns the JavaScript name of the elements.
	 *
	 * @access	public
	 *
	 * @return	string	The JavaScript name of the elements.
	 *
	 */
	public function getElement()
	{
		return '__' . preg_replace('/[^a-zA-Z0-9]/i', '_', $this->tagName);
	}

	/**
	 * Returns the JavaScript code of the DOM elements.
	 *
	 * <p>You should not call this method directly.</p>
	 *
	 * @access	public
	 *
	 * @return	string	JavaScript source code for the DOM elements.
	 *
	 * @static
	 *
	 */
	public function process()
	{
		$element = $this->getElement();

		$returnValue = $element . 's=document.getElementsByTagName("' . $this->tagName . '");';

		$returnValue .= 'for(' . $element . 'sIterator=0;';
		$returnValue .= $element . 'sIterator<' . $element . 's.length;';
		$returnValue .= $element . 'sIterator++){';
		$returnValue .= $element . '=' . $element . 's[' . $element . 'sIterator];';

		$returnValue .= parent::process($element);

		$returnValue .= '}';

		return $returnValue;
	}
}
?>