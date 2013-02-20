<?php
/**
 * XOAD HTML DOM Script Block file.
 *
 * <p>This file defines the {@link XOAD_HTML_DOM_ScriptBlock} Class.</p>
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
 * XOAD HTML DOM Script Block Class.
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
class XOAD_HTML_DOM_ScriptBlock
{
	/**
	 * Holds script block source code.
	 *
	 * @access	public
	 *
	 * @var		string
	 *
	 */
	public $script;

	/**
	 * Creates a new instance of the {@link XOAD_HTML_DOM_ScriptBlock} class.
	 *
	 * @param	string	$script	String that holds the JavaScript code.
	 *
	 * @access	public
	 *
	 */
	public function __construct($script = null)
	{
		$this->script = $script;
	}

	/**
	 * Returns the JavaScript code that the block contains.
	 *
	 * <p>You should not call this method directly.</p>
	 *
	 * @access	public
	 *
	 * @return	string	JavaScript source code for the script block.
	 *
	 * @static
	 *
	 */
	public function process()
	{
		return $this->script;
	}
}
?>