<?php

if (Api::getHttpRequestMethod () != Api::POST && Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!Social::singleton ()->socialNetworkExists ('Google'))
	throw new ApiException (__ ('Web application is not capable to register device user! Please, alert support team.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'Must enable Google Plus at Social Network configuration!');

if (!isset ($_POST ['email']) || trim ($_POST ['email']) == '' || 
	!isset ($_POST ['token']) || trim ($_POST ['token']) == '')
	throw new ApiException (__ ('Invalid parameters (e-mail or token)!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$email = trim ($_POST ['email']);
$device = isset ($_POST ['device']) && trim ($_POST ['device']) != '' ? $_POST ['device'] : __ ('Generic Mobile Device');

$token = $_auth->decrypt ($_POST ['token']);

$token = preg_replace ('/[^\x20-\x7f]/i', '', $token);

$url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='. $token;

$json = file_get_contents ($url);

if (trim ($json) == '')
	throw new ApiException (__ ('Invalid token!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$profile = (array) json_decode ($json);

if (!is_array ($profile) || !sizeof ($profile))
	throw new ApiException (__ ('Invalid user attributes!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

if (!isset ($profile ['email']) || trim ($profile ['email']) == '' || trim ($profile ['email']) != $email)
	throw new ApiException (__ ('Invalid token for this user!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'e-Mail address of token is different of parameter!');

$social = Social::singleton ()->getSocialNetwork ('Google');

$social->setProfile ($profile);

$id = $social->register ($profile);

if (!(int) $id)
	throw new ApiException (__ ('User is not registered in Web application!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);

$obj = MobileDevice::register ($device, $id);

$output = array ('id' => $obj->id,
				 'pk' => $_auth->encrypt ($obj->pk));

header ('Content-Type: application/json');

echo json_encode ((object) $output);