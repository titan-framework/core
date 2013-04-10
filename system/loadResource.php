<?
$instance = Instance::singleton ();

if (!Business::singleton ()->sectionExists ($_section))
	throw new Exception ('Invalid link! Unknown section.');

$file = Business::singleton ()->getSection ($_section)->getComponentPath () . '_resource/' . str_replace ('..', '', $_file);

if (!file_exists ($file))
	throw new Exception ('This file is not available!');

$controlMime = array (	'css' 	=> 'text/css', 
						'txt' 	=> 'text/plain', 
						'jpg' 	=> 'image/jpeg', 
						'jpeg' 	=> 'image/pjpeg', 
						'gif' 	=> 'image/gif', 
						'png' 	=> 'image/png', 
						'js' 	=> 'text/javascript',
						'ico'	=> 'image/x-icon');

$ext = array_pop (explode ('.', $file));

if (!array_key_exists ($ext, $controlMime))
	throw new Exception ('Permission denied for specified file type!');

$mimeType = $controlMime [$ext];

header ('Content-Type: '. $mimeType);
header ('Content-Disposition: inline; filename=' . md5 ($file));

if (!$instance->onDebugMode ())
{
	header ('Date: '. date ('D, j M Y G:i:s', filemtime ($file)) .' GMT');
	header ('Expires: '. gmdate ('D, j M Y H:i:s', time () + 15552000) .' GMT');
	header ('Cache-Control: must-revalidate');
	header ('Pragma: cache');
}

$binary	= fopen ($file, 'rb');

$buffer = fread ($binary, filesize ($file));

echo $buffer;
?>