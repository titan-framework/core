<?php

if (Api::getHttpRequestMethod () != Api::DELETE)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [1]) || trim ($_uri [1]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$user = $_auth->getUser ();

$id = trim ($_uri [1]);

$entity = new ApiEntity ('api-delete.xml', 'api.xml');

if (!$entity->load ($id))
	throw new ApiException (__ ('Unable to load data!'), ApiException::ERROR_SYSTEM, ApiException::INTERNAL_SERVER_ERROR);

$resume = $entity->getResume ();

if (!$entity->delete ($id))
	throw new ApiException (__ ('Unable to save the data submitted!'), ApiException::ERROR_SYSTEM, ApiException::INTERNAL_SERVER_ERROR);

Log::singleton ()->add ('DELETE', $resume);