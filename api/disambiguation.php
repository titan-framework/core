<?php

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

try
{
	$db = Database::singleton ();
	
	$query = $db->query ("SELECT currval ('". $db->getSchema () ."._disambiguation')");
	
	if (is_null ($query->fetchColumn ()))
		throw new ApiException (__ ('Disambiguation is not enable at this application!'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE);
}
catch (PDOException $e)
{
	if ($e->getCode () != '55000')
		throw new ApiException (__ ('Disambiguation is not enable at this application!'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE);
}

$sth = $db->prepare ("SELECT nextval ('". $db->getSchema () ."._disambiguation')");

$sth->execute ();

$result = $sth->fetch (PDO::FETCH_OBJ);

if ($result)
	echo $result->nextval;