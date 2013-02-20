<?php
/**
 * XOAD_Controls Extension File.
 *
 * <p>This file initialized the XOAD_Controls extension
 * and loads all necessary classes.</p>
 * <p>Note that this file is not included directly.
 * You should add the extension manually to the
 * extensions configuration file.</p>
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
 * Loads the file that defines the {@link XOAD_Controls} class.
 */
require_once(XOAD_CONTROLS_BASE . '/classes/Controls.class.php');

XOAD_Utilities::extensionHeader('controls', 'js/controls.js', 'js/controls_optimized.js');
XOAD_Utilities::customHeader('/extensions/js/cssQuery.js');
XOAD_Utilities::extensionHeader('controls', 'js/library/xoad.controls.js', 'js/library/xoad.controls_optimized.js');

?>