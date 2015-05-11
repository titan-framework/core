<?php

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [3]) || trim ($_uri [3]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$code = trim ($_uri [3]);

$db = Database::singleton ();

$sth = $db->prepare ("SELECT
						_code AS code,
						_name AS name,
						_mimetype AS mimetype,
						_size AS size,
						EXTRACT (EPOCH FROM _devise)::integer AS devise,
						EXTRACT (EPOCH FROM _change)::integer AS change,
						CASE WHEN _ready = B'1' THEN true ELSE false END AS ready
					  FROM _cloud WHERE _code = :code AND _deleted = B'0'");

$sth->bindParam (':code', $code, PDO::PARAM_STR);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	throw new ApiException (__ ('This file is not available!'), ApiException::ERROR_RESOURCE_MISSING, ApiException::NOT_FOUND);

echo json_encode ($obj);

try
{
	$sth = $db->prepare ("UPDATE _cloud SET _counter = _counter + 1 WHERE _code = :code");
	
	$sth->bindParam (':code', $obj->code, PDO::PARAM_STR);
	
	$sth->execute ();
}
catch (PDOException $e)
{}