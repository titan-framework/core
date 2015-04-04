<?php

if (!isset ($_GET ['fileId']) || !$_GET['fileId'] || !is_numeric ($_GET['fileId']))
	exit ();

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
	
	$sth = $db->prepare ("SELECT * FROM _file WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new Exception ();
	
	if (!is_null (@$obj->_public) && !(int) $obj->_public &&
	   (!isset ($_GET['hash']) || is_null (@$obj->_hash) || strlen (trim ($obj->_hash)) != 32 || $_GET['hash'] != $obj->_hash))
		throw new Exception ();
}
catch (PDOException $e)
{
	exit ();
}
catch (Exception $e)
{
	exit ();
}

if (isset ($_GET['assume']))
	$assume = (int) $_GET['assume'];
else
	$assume = $archive->getAssume ($obj->_mimetype);

if (!file_exists (File::getFilePath ($fileId)) && !file_exists (File::getLegacyFilePath ($fileId)))
	exit ();

$file = Instance::singleton ()->getCorePath () .'interface/file/' . $archive->getIcon ($obj->_mimetype) . '.gif';

$type = 'image/gif';

switch ($assume)
{
	case Archive::IMAGE:
		
		$file = File::resize ($fileId, $obj->_mimetype, $width, $height, TRUE, FALSE, TRUE);
		
		$type = $obj->_mimetype;
		
		break;
}

ob_clean ();

@apache_setenv ('no-gzip', 1);

@ini_set ('zlib.output_compression', 'Off');

$binary = fopen ($file, 'rb');

$buffer = fread ($binary, filesize ($file));

fclose ($binary);

header ('Content-Type: '. $type);

echo $buffer;