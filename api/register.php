<?php

if (Api::getHttpRequestMethod () != Api::POST && Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_POST ['token']) || trim ($_POST ['token']) == '')
	throw new ApiException (__ ('Invalid parameters (e-mail or token)!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$token = $_auth->decrypt ($_POST ['token']);

$token = preg_replace ('/[^\x20-\x7f]/i', '', $token);

define ('TITAN_FACEBOOK_ACCESS_TOKEN', $token);

if (!Social::isActive ())
	throw new ApiException (__ ('Web application is not capable to register device user! Please, alert support team.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'Must enable Google or Facebook at Social Network configuration!');

$driver = isset ($_POST ['driver']) && trim ($_POST ['driver']) != '' ? $_POST ['driver'] : 'Google';

if (!Social::singleton ()->socialNetworkExists ($driver))
	throw new ApiException (__ ('Web application is not capable to register device user! Please, alert support team.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'Must enable ['. $driver .'] at Social Network configuration!');

$device = isset ($_POST ['device']) && trim ($_POST ['device']) != '' ? $_POST ['device'] : __ ('Generic Mobile Device');

$id = null;

switch ($driver)
{
	case 'Google':

		$url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='. $token;

		$json = file_get_contents ($url);

		if (trim ($json) == '')
			throw new ApiException (__ ('Invalid token!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

		$profile = (array) json_decode ($json);

		if (!is_array ($profile) || !sizeof ($profile))
			throw new ApiException (__ ('Invalid user attributes!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

		$social = Social::singleton ()->getSocialNetwork ('Google');

		$social->setProfile ($profile);

		$id = $social->register ($profile);

		break;

	case 'Facebook':

		$social = Social::singleton ()->getSocialNetwork ('Facebook');

		if (!$social->authenticate ())
			throw new ApiException (__ ('Invalid token!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

		$profile = $social->loadProfile ();

		$id = $social->register ($profile);

		break;

	default:
		throw new ApiException (__ ('Web application is not capable to register device user! Please, alert support team.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'The driver for Social Network ['. $driver .'] is not supported yet!');
}

if (!(int) $id)
	throw new ApiException (__ ('User is not registered in Web application!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);

$obj = MobileDevice::register ($device, $id);

$output = array ('id' => $obj->id,
				 'pk' => $_auth->encrypt ($obj->pk));

header ('Content-Type: application/json');

echo json_encode ((object) $output);
