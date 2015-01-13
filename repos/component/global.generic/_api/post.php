<?php

if (Api::getHttpRequestMethod () != Api::POST)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

$user = $_auth->getUser ();

$entity = new ApiEntity ('api-post.xml', 'api.xml');

if (!$entity->recovery ())
	throw new ApiException (__ ('Unable to retrieve the data submitted!'), ApiException::ERROR_SYSTEM, ApiException::INTERNAL_SERVER_ERROR);

if (!$entity->save ($user))
	throw new ApiException (__ ('Unable to save the data submitted!'), ApiException::ERROR_SYSTEM, ApiException::INTERNAL_SERVER_ERROR);

Log::singleton ()->add ('CREATE', $entity->getResume ());