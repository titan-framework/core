<?php

if (Api::getHttpRequestMethod () != Api::POST)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_POST ['driver']) || trim ($_POST ['driver']) == '' ||
	!isset ($_POST ['email']) || trim ($_POST ['email']) == '' || 
	!isset ($_POST ['token']) || trim ($_POST ['token']) == '')
	throw new ApiException (__ ('Invalid parameters (driver, e-mail or token)!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$driver = trim ($_POST ['driver']);

if (!Social::singleton ()->socialNetworkExists ($driver))
	throw new ApiException (__ ('Web application is not capable to register device user! Please, alert support team.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'Must enable ['. $driver .'] at Social Network configuration!');

$email = trim ($_POST ['email']);

$validity = isset ($_POST ['validity']) && is_numeric ($_POST ['validity']) && (int) $_POST ['validity'] ? (int) $_POST ['validity'] : 3600;

$token = $_auth->decrypt ($_POST ['token']);

$token = preg_replace ('/[^\x20-\x7f]/i', '', $token);

switch ($driver)
{
	case 'Google':

		$url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='. $token;
		
		$json = file_get_contents ($url);
		
		if (trim ($json) == '')
			throw new ApiException (__ ('Invalid token!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);
		
		$profile = (array) json_decode ($json);
		
		break;
	
	case 'Facebook':
	default:
		throw new ApiException (__ ('Web application is not capable to register device user! Please, alert support team.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'The driver for Social Network ['. $driver .'] is not supported yet!');
}

if (!is_array ($profile) || !sizeof ($profile))
	throw new ApiException (__ ('Invalid user attributes!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

if (!isset ($profile ['email']) || trim ($profile ['email']) == '' || trim ($profile ['email']) != $email)
	throw new ApiException (__ ('Invalid token for this user!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'e-Mail address of token is different of parameter!');

$social = Social::singleton ()->getSocialNetwork ($driver);

$social->setProfile ($profile);

$id = $social->register ($profile);

if (!(int) $id)
	throw new ApiException (__ ('User is not registered in Web application!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);

$obj = BrowserDevice::register ($id, $validity);

echo json_encode ((object) array (
	'user' => $obj->user,
	'pk' => $_auth->encrypt ($obj->pk)
));