<?php

if (Api::getHttpRequestMethod () != Api::POST)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

$user = $_auth->getUser ();

$entity = new ApiEntity ('api-post.xml', 'api.xml');

if (!$entity->recovery ())
	throw new Exception (__ ('Unable to retrieve the data submitted!'));

if (!$entity->save ($user))
	throw new Exception (__ ('Unable to save the data submitted!'));

Log::singleton ()->add ('CREATE', $entity->getResume ());