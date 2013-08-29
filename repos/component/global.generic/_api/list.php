<?php

if (!isset ($_uri [2]) || !is_numeric ($_uri [2]))
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$_TIME = (int) $_uri [2];

$view = new View ('api.xml', 'list.xml');

$view->setPaginate (0);

if (!$view->load ($_TIME ." < extract (epoch from _update)"))
	throw new Exception (__ ('Unable to load data!'));

$json = array ();

while ($view->getItem ())
{
	$object = array ();
	
	$object [$view->getPrimary ()] = $view->getId ();
	
	while ($field = $view->getField ())
		if ($field->getAssign () == '_API_UPDATE_UNIX_TIMESTAMP_')
			$object [$field->getApiColumn ()] = $field->getUnixTime ();
		else
			$object [$field->getApiColumn ()] = Form::toText ($field);
	
	$json [] = (object) $object;
}

header ('Content-Type: application/json; charset=UTF-8');

echo json_encode ($json);