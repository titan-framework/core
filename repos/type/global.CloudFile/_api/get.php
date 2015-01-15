<?php

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [2]) || trim ($_uri [2]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$code = trim ($_uri [2]);

$db = Database::singleton ();

$sth = $db->prepare ("SELECT * FROM _cloud WHERE _code = :code AND _deleted = B'0' AND _ready = B'1'");

$sth->bindParam (':code', $code, PDO::PARAM_STR);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	throw new ApiException (__ ('This file is not available!'), ApiException::ERROR_RESOURCE_MISSING, ApiException::NOT_FOUND);

$archive = Archive::singleton ();

if (!$archive->isAcceptable ($obj->_mimetype))
	throw new ApiException (__ ('This file type is not supported!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$filePath = $archive->getDataPath () . 'cloud_' . str_pad ($obj->_id, 7, '0', STR_PAD_LEFT);

if (!file_exists ($filePath))
	throw new ApiException (__ ('This file is not available!'), ApiException::ERROR_RESOURCE_MISSING, ApiException::NOT_FOUND);
		
$contentType = $obj->_mimetype;

header ('Content-Type: '. $contentType);
header ('Content-Disposition: attachment; filename=' . fileName ($obj->_name));

$binary = fopen ($filePath, 'rb');

$buffer = fread ($binary, filesize ($filePath));

fclose ($binary);

echo $buffer;

try
{
	$sth = $db->prepare ("UPDATE _cloud SET _counter = _counter + 1 WHERE _id = :id");
	
	$sth->bindParam (':id', $obj->_id, PDO::PARAM_INT);
	
	$sth->execute ();
}
catch (PDOException $e)
{}