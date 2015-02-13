<?php

if (!isset ($_GET ['id']) || !is_numeric ($_GET['id']) || !$_GET['id'] ||
	!isset ($_GET ['hash']) || strlen (preg_replace ('/[^0-9a-zA-Z]/i', '', $_GET ['hash'])) != 32)
	exit ();

ob_clean ();

set_time_limit (0);

@ini_set ('memory_limit', '-1');

@apache_setenv ('no-gzip', 1);

@ini_set ('zlib.output_compression', 'Off');

require __DIR__ . DIRECTORY_SEPARATOR .'streaming.php';

$id = (int) $_GET ['id'];
$hash = $_GET ['hash'];

try
{
	if (!$id)
		throw new Exception ('Invalid file!');
	
	$sth = Database::singleton ()->prepare ("SELECT * FROM _ckeditor WHERE _id = :id AND _hash = :hash");
	
	$sth->bindParam (':id', $id, PDO::PARAM_INT);
	$sth->bindParam (':hash', $hash, PDO::PARAM_INT, 32);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new Exception ('This file is not available!');
	
	if (!Archive::singleton ()->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type ('. $obj->_mimetype .') is not supported!');
	
	$playable = CKEditor::getPlayableFile ($id, $obj->_mimetype);
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

$stream = new VideoStream ($playable);

$stream->start ();