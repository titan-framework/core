<?php

if (!User::singleton ()->isLogged ())
	exit ();

if (!isset ($_GET ['fileId']) || !$_GET['fileId'] || !is_numeric ($_GET['fileId']))
	exit ();

ob_clean ();

set_time_limit (0);

@ini_set ('memory_limit', '-1');

if (function_exists ('apache_setenv'))
	@apache_setenv ('no-gzip', 1);

@ini_set ('zlib.output_compression', 'Off');

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
	
	if (!Archive::singleton ()->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type ('. $obj->mimetype .') is not supported!');
	
	$playable = CloudFile::getPlayableFile ($fileId, $obj->_mimetype);
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
	
	die ('Critical error! See LOG file.');
}
catch (Exception $e)
{
	toLog ($e->getMessage ());
	
	die ($e->getMessage ());
}

try
{
	$sth = $db->prepare ("UPDATE _cloud SET _counter = _counter + 1 WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
}
catch (PDOException $e)
{}

$stream = new VideoStream ($playable);

$stream->start ();