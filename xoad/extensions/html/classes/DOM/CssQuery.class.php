<?php
/**
 * XOAD HTML DOM cssQuery file.
 *
 * <p>This file defines the {@link XOAD_HTML_DOM_CssQuery} Class.</p>
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
 * Loads the file that defines the base class for {@link XOAD_HTML_DOM_CssQuery}.
 */
require_once(XOAD_HTML_BASE . '/classes/DOM/BaseElement.class.php');

/**
 * XOAD HTML DOM cssQuery Class.
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
class XOAD_HTML_DOM_CssQuery extends XOAD_HTML_DOM_BaseElement
{
	/**
	 * Creates a new instance of the {@link XOAD_HTML_DOM_CssQuery} class.
	 *
	 * @param	string	$query	String representing the value of
	 *							the cssQuery.
	 *
	 * @access	public
	 *
	 */
	//public function XOAD_HTML_DOM_CssQuery($query)
	public function __construct($query)
	{
		parent::__construct();

		$this->query = $query;

		$this->skipKeys[] = 'query';
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
		return '__' . md5(uniqid(rand(), true));
	}

	/**
	 * Returns the JavaScript code of the cssQuery elements.
	 *
	 * <p>You should not call this method directly.</p>
	 *
	 * @access	public
	 *
	 * @return	string	JavaScript source code for the cssQuery elements.
	 *
	 * @static
	 *
	 */
	public function process()
	{
		$element = $this->getElement();

		$returnValue = $element . 's=cssQuery("' . addslashes($this->query) . '");';

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