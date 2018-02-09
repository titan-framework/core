<?php

if (!isset ($_uri [2]) || !is_numeric ($_uri [2]))
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'You need to pass a unix timestamp to endpoint!');

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

$_USER = $_auth->getUser ();

if (!is_integer ($_USER) || !$_USER)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to recognize a user.');

$_TIME = (int) $_uri [2];

$entity = new ApiList ('api-list.xml', 'api-get.xml', 'api.xml');

$owners = array ('_user', '_author');

$mandatory = Database::getMandatoryColumns ($entity->getTable ());

$columns = array_intersect ($owners, $mandatory);

if (!sizeof ($columns))
	throw new ApiException (__ ('Is not possible to identify your data!'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'This endpoint requires that all entity data have a owner ("_user" and "_author" columns does not exists)!');

if (!$entity->load ($_TIME ." < EXTRACT (EPOCH FROM _update)::integer AND '". $_USER ."' IN (". implode (",", $columns) .")"))
	throw new ApiException (__ ('Unable to load data!'), ApiException::ERROR_SYSTEM, ApiException::INTERNAL_SERVER_ERROR);

$json = array ();

while ($entity->getItem ())
{
	$itemId = $entity->getId ();

	$object = array ();

	if ($entity->useCode ())
		$object [$entity->getCodeColumn ()] = $entity->getCode ();
	else
		$object [$entity->getPrimary ()] = $itemId;

	while ($field = $entity->getField ())
		$object [$field->getApiColumn ()] = ApiEntity::toApi ($field, $itemId);

	$json [] = (object) $object;
}

header ('Content-Type: application/json; charset=UTF-8');

echo json_encode ($json);
