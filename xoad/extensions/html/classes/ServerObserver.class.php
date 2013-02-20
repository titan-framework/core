<?php
/**
 * XOAD HTML Server Observer file.
 *
 * <p>This file defines the {@link XOAD_HTML_ServerObserver} Class.</p>
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
 * XOAD HTML Server Observer Class.
 *
 * <p>This class is used by the {@link XOAD_HTML} extension
 * to process server events.</p>
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
class XOAD_HTML_ServerObserver extends XOAD_Observer
{
	/**
	 * This method is called after {@link XOAD_Server::notifyObservers}
	 * is called.
	 *
	 * @access	public
	 *
	 * @return	bool	Always true.
	 *
	 */
	public function updateObserver($event, $arguments)
	{
		if ($event == 'dispatchLeave') {

			if (array_key_exists('returnValue', $arguments['response'])) {

				$arguments['response']['html'] = XOAD_HTML::process();
			}
		}

		return true;
	}
}
?>