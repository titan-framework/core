<?php

if (!User::singleton ()->isLogged ())
	exit ();

if (!isset ($_GET ['fileId']) || !$_GET['fileId'] || !is_numeric ($_GET['fileId']))
	exit ();

$fileId = (int) $_GET ['fileId'];

try
{
	if (!$fileId)
		throw new Exception ('Invalid file!');
	
	$db = Database::singleton ();
	
	$sth = $db->prepare ("SELECT * FROM _cloud WHERE _id = :id AND _ready = B'1' AND _deleted = B'0'");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new Exception ('This file is not available!');
	
	$archive = Archive::singleton ();
	
	if (!$archive->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type ('. $obj->mimetype .') is not supported!');
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
	
	die ('Critical error!');
}
catch (Exception $e)
{
	die ($e->getMessage ());
}

if (isset ($_GET['assume']))
	$assume = (int) $_GET['assume'];
else
	$assume = $archive->getAssume ($obj->_mimetype);

$filePath = CloudFile::getFilePath ($fileId);

if (!file_exists ($filePath))
	die ('This file is not available!');
		
$contentType = $obj->_mimetype;

switch ($assume)
{
	case Archive::IMAGE:
	case Archive::OPEN:
		header ('Content-Type: '. $contentType);
		header ('Content-Disposition: inline; filename=' . fileName ($obj->_name));
		break;
	
	case Archive::DOWNLOAD:
	case Archive::VIDEO:
	case Archive::AUDIO:
	default:
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename=' . fileName ($obj->_name));
		break;
}

ob_clean ();

@apache_setenv ('no-gzip', 1);

@ini_set ('zlib.output_compression', 'Off');

$binary = fopen ($filePath, 'rb');

$buffer = fread ($binary, filesize ($filePath));

fclose ($binary);

echo $buffer;

try
{
	$sth = $db->prepare ("UPDATE _cloud SET _counter = _counter + 1 WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
}
catch (PDOException $e)
{}