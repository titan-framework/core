<?php

if (!isset ($_uri [2]) || !is_numeric ($_uri [2]))
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

$_TIME = (int) $_uri [2];

$entity = new ApiEntity ('api.xml');

$update = $entity->getField ('_API_UPDATE_UNIX_TIMESTAMP_');

if (is_object ($update))
	$columnUp = $update->getTable () .'.'. $update->getColumn ();
else
	$columnUp = $view->getTable () . '._update';

if (!$entity->load ($_TIME ." < extract (epoch from ". $columnUp .")"))
	throw new Exception (__ ('Unable to load data!'));

$json = array ();

while ($entity->getItem ())
{
	$itemId = $entity->getId ();
	
	$object = array ();
	
	$object [$entity->getPrimary ()] = $itemId;
	
	while ($field = $entity->getField ())
		if ($field->getAssign () == '_API_UPDATE_UNIX_TIMESTAMP_')
			$object [$field->getApiColumn ()] = $field->getUnixTime ();
		else
			$object [$field->getApiColumn ()] = ApiEntity::toApi ($field, $itemId);
	
	$json [] = (object) $object;
}

header ('Content-Type: application/json; charset=UTF-8');

echo json_encode ($json);