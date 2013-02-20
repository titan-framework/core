<?php
/**
 * XOAD Observer file.
 *
 * <p>This file defines the {@link XOAD_Observer} Class.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * class CallbackObserver extends XOAD_Observer
 * {
 * 	function updateObserver($event, $arg)
 * 	{
 * 		print $event . ' called.';
 * 	}
 * }
 *
 * XOAD_Server::addObserver(new CallbackObserver());
 *
 * ...
 *
 * ?>
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
 * XOAD Observer Class.
 *
 * <p>To observe XOAD events you must define your own
 * classes that extend the {@link XOAD_Observer} class.</p>
 * <p>See {@link XOAD_Observer::updateObserver} for
 * more information.</p>
 * <p>Example:</p>
 * <code>
 * <?php
 *
 * require_once('xoad.php');
 *
 * class CallbackObserver extends XOAD_Observer
 * {
 * 	function updateObserver($event, $arg)
 * 	{
 * 		print $event . ' called.';
 * 	}
 * }
 *
 * XOAD_Server::addObserver(new CallbackObserver());
 *
 * ...
 *
 * ?>
 * </code>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Observer
{
	/**
	 * This method is called when {@link XOAD_Observable::notifyObservers}
	 * is called.
	 *
	 * <p>You should override this method to accept two parameters - the
	 * event name and the event argument.</p>
	 * <p>If {@link XOAD_Observable::notifyObservers} is called without
	 * parameters the event name is 'default'.</p>
	 * <p>You should also always return a boolean value that indicates
	 * the result of the event.</p>
	 *
	 * @access	public
	 *
	 * @return	bool	Always true.
	 *
	 */
	public function updateObserver($event, $arguments)
	{
		return true;
	}
}

?>