<?php

if (!isset ($_GET ['fileId']) || !$_GET ['fileId'] || !is_numeric ($_GET ['fileId']))
	exit ();

set_error_handler ('logPhpError');

ob_clean ();

set_time_limit (0);

@ini_set ('memory_limit', '-1');

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
	
	if (!Archive::singleton ()->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type ('. $obj->mimetype .') is not supported!');
	
	CloudFile::getPlayableFile ($fileId, $obj->_mimetype);
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
}
catch (Exception $e)
{
	toLog ($e->getMessage ());
}

restore_error_handler ();