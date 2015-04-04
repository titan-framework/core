<?php

if (!isset ($_GET ['id']) || !$_GET['id'] || !is_numeric ($_GET['id']))
	exit ();

$fileId = (int) $_GET ['id'];

try
{
	if (!$fileId)
		throw new Exception ('Invalid file!');
	
	$db = Database::singleton ();
	
	$sth = $db->prepare ("SELECT * FROM _file WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new Exception ('This file is not available!');
	
	if (!is_null (@$obj->_public) && !(int) $obj->_public &&
	   (!isset ($_GET['hash']) || is_null (@$obj->_hash) || strlen (trim ($obj->_hash)) != 32 || $_GET['hash'] != $obj->_hash))
		throw new Exception (__ ('You do not have permission to access this file!'));
	
	$archive = Archive::singleton ();
	
	if (!$archive->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type ('. $obj->mimetype .') is not supported!');
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
	
	throw new Exception ('Critical database error!');
}

if (isset ($_GET['assume']))
	$assume = (int) $_GET['assume'];
else
	$assume = $archive->getAssume ($obj->_mimetype);

$filePath = File::getFilePath ($fileId);

if (!file_exists ($filePath))
	$filePath = File::getLegacyFilePath ($fileId);

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
	$sth = $db->prepare ("UPDATE _file SET _counter = _counter + 1 WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
}
catch (PDOException $e)
{}