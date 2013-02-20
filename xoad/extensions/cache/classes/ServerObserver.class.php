<?php
/**
 * XOAD Cache Server Observer file.
 *
 * <p>This file defines the {@link XOAD_Cache_ServerObserver} Class.</p>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Cache
 *
 * @version		0.6.0.0
 *
 */

/**
 * XOAD Cache Server Observer Class.
 *
 * <p>This class is used by the {@link XOAD_Cache} extension
 * to process server events.</p>
 *
 * @author		Stanimir Angeloff
 *
 * @package		XOAD
 *
 * @subpackage	XOAD_Cache
 *
 * @version		0.6.0.0
 *
 */
class XOAD_Cache_ServerObserver extends XOAD_Observer
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
		if ($event == 'initializeCallbackSuccess') {

			if (array_key_exists('source', $arguments['request'])) {

				if (XOAD_Cache::initialize($arguments['request'])) {

					exit;
				}
			}

		} else if ($event == 'dispatchLeave') {

			if (array_key_exists('returnValue', $arguments['response'])) {

				if (defined('XOAD_CACHE_REQUEST')) {

					XOAD_Cache::cacheRequest($arguments['request'], $arguments['response']);
				}
			}
		}

		return true;
	}
}
?>