<?php

if (Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [1]) || trim ($_uri [1]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$user = $_auth->getUser ();

$id = trim ($_uri [1]);

$entity = new ApiEntity ('api-put.xml', 'api.xml');

if (!$entity->recovery ())
	throw new Exception (__ ('Unable to retrieve the data submitted!'));

if (!$entity->save ($user, $id))
	throw new Exception (__ ('Unable to save the data submitted!'));

Log::singleton ()->add ('EDIT', $entity->getResume ());