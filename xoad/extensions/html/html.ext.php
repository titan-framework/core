<?php
/**
 * XOAD_HTML Extension File.
 *
 * <p>This file initialized the XOAD_HTML extension
 * and installs all necessary server observers.</p>
 * <p>Note that this file is not included directly.
 * You should add the extension manually to the
 * extensions configuration file.</p>
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
 * Load the file that defines the XOAD_HTML Server observer.
 */
require_once(XOAD_HTML_BASE . '/classes/ServerObserver.class.php');

/**
 * Loads the file that defines the {@link XOAD_HTML} class.
 */
require_once(XOAD_HTML_BASE . '/classes/HTML.class.php');

XOAD_Server::addObserver(new XOAD_HTML_ServerObserver());

XOAD_Utilities::extensionHeader('html', 'js/html.js', 'js/html_optimized.js');
XOAD_Utilities::customHeader('/extensions/js/cssQuery.js');

?>