<?php

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

$array = Alert::singleton ()->getAlerts ($user);

$array = array_reverse ($array, TRUE);

$json = array ();

foreach ($array as $key => $value)
{
	$icon = strtoupper (array_shift (explode ('.', array_pop (explode ('/', $value ['_ICON_'])))));
	
	$json [] = (object) array ('id' => $key, 'message' => $value ['_MESSAGE_'], 'icon' => $icon, 'read' => $value ['_READ_']);
}

header ('Content-Type: application/json');

echo json_encode ($json);