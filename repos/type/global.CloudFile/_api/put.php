<?php

if (Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

require __DIR__ . DIRECTORY_SEPARATOR .'template'. DIRECTORY_SEPARATOR .'send.php';