<?php

if (!isset ($_file) || !file_exists ($_file))
	throw new Exception ('This file is not available!');

$controlMime = array (
	'css' 	=> 'text/css',
	'txt' 	=> 'text/plain',
	'jpg' 	=> 'image/jpeg',
	'jpeg' 	=> 'image/pjpeg',
	'gif' 	=> 'image/gif',
	'png' 	=> 'image/png',
	'js' 	=> 'text/javascript',
	'ico'	=> 'image/x-icon',
	'swf'	=> 'application/x-shockwave-flash'
);

$ext = array_pop (explode ('.', $_file));

if (!array_key_exists ($ext, $controlMime))
	throw new Exception ('Permission denied for specified file type!');

$mimeType = $controlMime [$ext];

header ('Content-Type: '. $mimeType);
header ('Content-Disposition: inline; filename=' . md5 ($_file));

if (!Instance::singleton ()->onDebugMode ())
{
	header ('Date: '. date ('D, j M Y G:i:s', filemtime ($_file)) .' GMT');
	header ('Expires: '. gmdate ('D, j M Y H:i:s', time () + 15552000) .' GMT');
	header ('Cache-Control: must-revalidate');
	header ('Pragma: cache');
}

$handle	= fopen ($_file, 'rb');

$buffer = fread ($handle, filesize ($_file));

fclose ($handle);

echo $buffer;
