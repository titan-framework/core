<?php

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

$entity = new ApiEntity ('api.xml');

if ($entity->useCode ())
	$sql = "SELECT ". $entity->getCodeColumn () ." FROM ". $entity->getTable ();
else
	$sql = "SELECT ". $entity->getPrimary () ." FROM ". $entity->getTable ();

$sth = Database::singleton ()->prepare ($sql);

$sth->execute ();

header ('Content-Type: application/json; charset=UTF-8');

echo json_encode (implode (',', $sth->fetchAll (PDO::FETCH_COLUMN)));