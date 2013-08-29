<?php

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$array = Alert::singleton ()->getAlerts ($user);

$json = array ();

foreach ($array as $key => $value)
{
	$icon = strtoupper (array_shift (explode ('.', array_pop (explode ('/', $value ['_ICON_'])))));
	
	$json [] = (object) array ('id' => $key, 'message' => $value ['_MESSAGE_'], 'icon' => $icon, 'read' => $value ['_READ_']);
}

header ('Content-Type: application/json');

echo json_encode ($json);