<?php
/*
 * PHP QR Code encoder
 *
 * Config file, feel free to modify
 */
 
/* Default configuration
 
define('QR_CACHEABLE', true);                                                               // use cache - more disk reads but less CPU power, masks and format templates are stored there
define('QR_CACHE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR);  // used when QR_CACHEABLE === true
define('QR_LOG_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);                                // default error logs dir   

define('QR_FIND_BEST_MASK', true);                                                          // if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code
define('QR_FIND_FROM_RANDOM', false);                                                       // if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
define('QR_DEFAULT_MASK', 2);                                                               // when QR_FIND_BEST_MASK === false
											  
define('QR_PNG_MAXIMUM_SIZE',  1024);                                                       // maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images
 */

/* Modifications for Titan Framework */

define('QR_CACHEABLE', TRUE);
define('QR_CACHE_DIR', Instance::singleton ()->getCachePath () .'qr'. DIRECTORY_SEPARATOR);
define('QR_LOG_DIR', Instance::singleton ()->getCachePath () .'qr'. DIRECTORY_SEPARATOR .'log'. DIRECTORY_SEPARATOR);
define('QR_FIND_BEST_MASK', TRUE);
define('QR_FIND_FROM_RANDOM', FALSE);
define('QR_DEFAULT_MASK', 2);
define('QR_PNG_MAXIMUM_SIZE', 1024);

if (!is_dir (QR_CACHE_DIR) && !@mkdir (QR_CACHE_DIR, 0777))
	throw new Exception ('Impossible to create directory ['. QR_CACHE_DIR .'].');

if (!is_dir (QR_LOG_DIR) && !@mkdir (QR_LOG_DIR, 0777))
	throw new Exception ('Impossible to create directory ['. QR_LOG_DIR .'].');

/* End of Titan modifications */