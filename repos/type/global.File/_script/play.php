﻿<?php

if (!isset ($_GET ['id']) || !$_GET['id'] || !is_numeric ($_GET['id']))
	exit ();

ob_end_clean ();

set_time_limit (0);

@ini_set ('memory_limit', '-1');

if (function_exists ('apache_setenv'))
	@apache_setenv ('no-gzip', 1);

@ini_set ('zlib.output_compression', 'Off');

require __DIR__ . DIRECTORY_SEPARATOR .'streaming.php';

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
	
	if (!Archive::singleton ()->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type ('. $obj->mimetype .') is not supported!');
	
	$playable = File::getPlayableFile ($fileId, $obj->_mimetype);
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
	
	throw new Exception ('Critical error! See LOG file.');
}

try
{
	$sth = $db->prepare ("UPDATE _file SET _counter = _counter + 1 WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
}
catch (PDOException $e)
{}

$stream = new VideoStream ($playable);

$stream->start ();