<?php
if (!isset ($_GET ['fileId']) || !$_GET['fileId'] || !is_numeric ($_GET['fileId']))
	die ();

$archive = Archive::singleton ();

if (isset ($_GET['width']) && is_numeric ($_GET['width']))
	$width = (int) $_GET['width'];
else
	$width = 0;

if (isset ($_GET['height']) && is_numeric ($_GET['height']))
	$height = (int) $_GET['height'];
else
	$height = 0;

$fileId = (int) $_GET ['fileId'];

try
{
	$db = Database::singleton ();
	
	$sth = $db->prepare ("SELECT _name, _size, _mimetype FROM _file WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new Exception ();
}
catch (PDOException $e)
{
	die ();
}
catch (Exception $e)
{
	die ();
}

if (isset ($_GET['assume']))
	$assume = (int) $_GET['assume'];
else
	$assume = $archive->getAssume ($obj->_mimetype);

if (!file_exists ($archive->getDataPath () . 'file_' . str_pad ($fileId, 7, '0', STR_PAD_LEFT)) &&
	!file_exists ($archive->getDataPath () . 'file_' . str_pad ($fileId, 19, '0', STR_PAD_LEFT)))
	die ();

$filePath = Instance::singleton ()->getCorePath () .'interface/file/' . $archive->getIcon ($obj->_mimetype) . '.gif';

$contentType = 'image/png';

switch ($assume)
{
	case Archive::IMAGE:
		$filePath = $archive->getDataPath () . 'file_' . str_pad ($fileId, 19, '0', STR_PAD_LEFT);
		
		if (!file_exists ($filePath))
			$filePath = $archive->getDataPath () . 'file_' . str_pad ($fileId, 7, '0', STR_PAD_LEFT);
		
		$contentType = $obj->_mimetype;
		
		if ($width && $height)
			resize ($filePath, $contentType, $width, $height, TRUE);
		
		if ($width || $height)
			resize ($filePath, $contentType, $width, $height);
		
		break;
}

if (!file_exists ($filePath))
	die ();

$binary = fopen ($filePath, 'rb');

$buffer = fread ($binary, filesize ($filePath));

fclose ($binary);

header ('Content-Type: '. $contentType);

echo $buffer;
?>