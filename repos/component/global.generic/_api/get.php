<?php

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [1]) || trim ($_uri [1]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$user = $_auth->getUser ();

$id = trim ($_uri [1]);

$entity = new ApiEntity ('api-get.xml', 'api.xml');

if (!$entity->load ($id))
	throw new Exception (__ ('Unable to load data!'));

echo $entity->json ();