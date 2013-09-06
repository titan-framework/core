<?php

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

if (!isset ($_uri [1]) || !is_numeric ($_uri [1]) || !(int) $_uri [1])
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$id = (int) $_uri [1];

if (Api::getHttpRequestMethod () == Api::DELETE)
{
	Alert::singleton ()->delete ($id, $user);
	
	exit ();
}

if (Api::getHttpRequestMethod () == Api::POST)
{
	Alert::singleton ()->read ($id, $user);
	
	exit ();
}

throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);