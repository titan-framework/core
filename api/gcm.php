<?php

if (Api::getHttpRequestMethod () != Api::POST && Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!$_auth->hasContext ('CLIENT', 'CLIENT-AS-USER'))
	throw new ApiException (__ ('This application do not use client authentication, so is not necessary (or possible) store Registration ID for Google Cloud Message!'), ApiException::ERROR_CLIENT_AUTH, ApiException::NOT_ACCEPTABLE);

if (!isset ($_POST ['gcm']) || trim ($_POST ['gcm']) == '')
	throw new ApiException (__ ('Registration ID of Google Cloud Message for device is missing or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$device = $_auth->registerGoogleCloudMessage ($_POST ['gcm']);

if ($device === FALSE)
	throw new ApiException (__ ('Impossible to registry device! Please, contact administrator.'), ApiException::ERROR_NOT_FOUND, ApiException::NOT_FOUND);

header ('Content-Type: application/json');

echo json_encode (array ('device' => $device));