<?php

if (!isset ($_GET ['user']) || !is_numeric ($_GET['user']) || !(int) $_GET['user'])
	throw new ApiException ('Invalid URI!', ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$user = (int) $_GET['user'];

$sth = Database::singleton ()->prepare ("SELECT * FROM _user WHERE _id = :id LIMIT 1");

$sth->bindParam (':id', $user, PDO::PARAM_INT);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

$columns = array ('photo', 'picture');

$photo = NULL;

foreach ($columns as $trash => $column)
	if (!is_null (@$obj->$column) && is_integer ($obj->$column) && (int) $obj->$column)
	{
		$photo = (int) $obj->$column;
		
		break;
	}

if (is_null ($photo))
	throw new ApiException ('The user does not have any photo!', ApiException::ERROR_RESOURCE_MISSING, ApiException::NOT_FOUND);

if (isset ($_GET['width']) && is_numeric ($_GET['width']))
	$width = (int) $_GET['width'];
else
	$width = 0;

if (isset ($_GET['height']) && is_numeric ($_GET['height']))
	$height = (int) $_GET['height'];
else
	$height = 0;
	
$force = isset ($_GET['force']) && $_GET['force'] == '1' ? TRUE : FALSE;

$bw = isset ($_GET['bw']) && $_GET['bw'] == '1' ? TRUE : FALSE;

try
{
	$db = Database::singleton ();
	
	$sth = $db->prepare ("SELECT * FROM _file WHERE _id = :id");
	
	$sth->bindParam (':id', $photo, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new ApiException ('The user does not have any photo!', ApiException::ERROR_RESOURCE_MISSING, ApiException::NOT_FOUND);
	
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
	
	throw new ApiException ('Database error!', ApiException::ERROR_DB);
}
catch (Exception $e)
{
	toLog ($e->getMessage ());
	
	throw new ApiException ('System error!', ApiException::ERROR_SYSTEM);
}

require_once Instance::singleton ()->getReposPath () .'type'. DIRECTORY_SEPARATOR .'global.File'. DIRECTORY_SEPARATOR .'File.php';

$path = File::getFilePath ($photo);

if (!file_exists ($path))
	$path = File::getLegacyFilePath ($photo);

if (!file_exists ($path))
	throw new ApiException ('The user does not have any photo!', ApiException::ERROR_RESOURCE_MISSING, ApiException::NOT_FOUND);
		
$mime = $obj->_mimetype;

if ($width || $height || $bw)
	resize ($path, $mime, $width, $height, $force, $bw);

header ('Content-Type: '. $mime);
header ('Content-Disposition: inline; filename=' . fileName ($obj->_name));

$binary = fopen ($path, 'rb');

$buffer = fread ($binary, filesize ($path));

fclose ($binary);

echo $buffer;

try
{
	$sth = $db->prepare ("UPDATE _file SET _counter = _counter + 1 WHERE _id = :id");
	
	$sth->bindParam (':id', $photo, PDO::PARAM_INT);
	
	$sth->execute ();
}
catch (PDOException $e)
{}

exit ();