<?php

if (!isset ($_GET ['fileId']) || !$_GET['fileId'] || !is_numeric ($_GET['fileId']))
	exit ();

require __DIR__ . DIRECTORY_SEPARATOR .'streaming.php';

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
	
	if (!is_null (@$obj->_public) && !(int) $obj->_public)
	{
		// TODO: permission control
		// throw new Exception ('Permission denied!');
	}
	
	$archive = Archive::singleton ();
	
	if (!$archive->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type is not supported!');
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

$filePath = $archive->getDataPath () . 'cloud_' . str_pad ($fileId, 7, '0', STR_PAD_LEFT);

if (!file_exists ($filePath))
	die ('This file is not available!');

try
{
	$sth = $db->prepare ("UPDATE _cloud SET _counter = _counter + 1 WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
}
catch (PDOException $e)
{}

$stream = new VideoStream ($filePath);

$stream->start ();