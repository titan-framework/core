<?php

try
{
	set_error_handler ('apiPhpError', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

	if (!isset ($_GET ['uri']))
		throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid URI!');

	if (!Api::isActive ())
		throw new ApiException (__ ('Application API is not active!'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE);

	$_auth = Api::singleton ()->getActiveApp ();

	if (!is_object ($_auth))
		throw new ApiException (__ ('Invalid credentials!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'This application is not enable in system!');

	if (!$_auth->isAccessibleEndpoint ($_GET['uri']))
		throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'This endpoint is not accessible by application!');

	require_once Instance::singleton ()->getCorePath () .'extra'. DIRECTORY_SEPARATOR .'Blowfish.php';

	$_uri = explode ('/', $_GET['uri']);

	$forRegister = array ('register', 'social', 'browser', 'status', 'pin', 'login');

	if (!in_array ($_uri [0], $forRegister))
		$_auth->authenticate ();
	else
		$_auth->authenticateForRegister ();
	
	// throw new Exception (print_r ($_auth));

	if (isset ($_GET['language']) && trim ($_GET['language']) != '')
		Localization::singleton ()->setLanguage ($_GET['language']);

	switch ($_uri [0])
	{
		case 'auth':
		case 'user':

			require $corePath .'api/user.php';

			break;
		
		case 'pin':

			require $corePath .'api/pin.php';

			break;
		
		case 'login':

			require $corePath .'api/login.php';

			break;

		case 'remove':

			require $corePath .'api/remove.php';

			break;

		case 'register':

			require $corePath .'api/register.php';

			break;

		case 'social':

			require $corePath .'api/social.php';

			break;

		case 'alerts':

			require $corePath .'api/alerts.php';

			break;

		case 'alert':

			require $corePath .'api/alert.php';

			break;

		case 'gcm':

			require $corePath .'api/gcm.php';

			break;

		case 'disambiguation':

			require $corePath .'api/disambiguation.php';

			break;

		case 'status':

			require $corePath .'api/status.php';

			break;

		case 'type':

			$_type = ucfirst (preg_replace_callback ("/\-./", create_function ('$matches', 'return strtoupper($matches[0][1]);'), trim (@$_uri [1])));

			if (!Instance::singleton ()->typeExists ($_type))
				throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Type ['. $_type .'] do not exists!');

			$path = Instance::singleton ()->getTypePath ($_type) .'_api'. DIRECTORY_SEPARATOR;

			$_service = str_replace ('..', '', trim (@$_uri [2]));

			if (trim ($_service) == '' || !file_exists ($path . $_service .'.php'))
				$_service = strtolower (Api::getHttpRequestMethod ());

			if (trim ($_service) == '' || !file_exists ($path . $_service .'.php'))
				throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED, $path . $_service .'.php');

			if (Api::getHttpRequestMethod () == Api::PUT || Api::getHttpRequestMethod () == Api::PATCH)
				retrievePut ();

			convertApiParametersToUtf8 ();

			require $path . $_service .'.php';

			break;

		default:

			if (!Business::singleton ()->sectionExists ($_uri [0]))
				throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Section ['. $_uri [0] .'] do not exists!');

			$_section = Business::singleton ()->getSection ($_uri [0]);

			if (!is_object ($_section))
				throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

			$_action = $_section->getAction (Action::TAPI);

			Business::singleton ()->setCurrent ($_section, $_action);

			foreach (Instance::singleton ()->getTypes () as $type => $path)
				require_once $path . $type .'.php';

			if (file_exists ($_section->getCompPath () .'_class.php'))
				include $_section->getCompPath () .'_class.php';

			if (file_exists ($_section->getCompPath () .'_function.php'))
				include $_section->getCompPath () .'_function.php';

			$path = $_section->getComponentPath () .'_api'. DIRECTORY_SEPARATOR;

			$_service = str_replace ('..', '', trim (@$_uri [1]));

			if (trim ($_service) == '' || !file_exists ($path . $_service .'.php'))
				$_service = strtolower (Api::getHttpRequestMethod ());

			if (trim ($_service) == '' || !file_exists ($path . $_service .'.php'))
				throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

			if (Api::getHttpRequestMethod () == Api::PUT || Api::getHttpRequestMethod () == Api::PATCH)
				retrievePut ();

			convertApiParametersToUtf8 ();

			require $path . $_service .'.php';
	}

	restore_error_handler ();
}
catch (ApiException $e)
{
	header ('HTTP/1.1 '. $e->getCode () .' '. ApiException::$status [$e->getCode ()]);
	header ('Content-Type: application/json');

	$array = array ('ERROR' => $e->getTitanErrorCode (),
					'MESSAGE' => $e->getMessage (),
					'TECHNICAL' => $e->getTitanTechnical ());

	echo json_encode ($array);
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());

	header ('HTTP/1.1 '. ApiException::INTERNAL_SERVER_ERROR .' '. ApiException::$status [ApiException::INTERNAL_SERVER_ERROR]);
	header ('Content-Type: application/json');

	$array = array ('ERROR' => ApiException::ERROR_DB,
					'MESSAGE' => __ ('Database error! Please, contact administrator.'),
					'TECHNICAL' => '[Line #'. $e->getLine () .'] '. $e->getMessage ());

	echo json_encode ($array);
}
catch (Exception $e)
{
	toLog ($e->getMessage ());

	header ('HTTP/1.1 '. ApiException::INTERNAL_SERVER_ERROR .' '. ApiException::$status [ApiException::INTERNAL_SERVER_ERROR]);
	header ('Content-Type: application/json');

	$array = array ('ERROR' => ApiException::ERROR_SYSTEM,
					'MESSAGE' => 'System error! Please, contact administrator.',
					'TECHNICAL' => '[Line #'. $e->getLine () .'] '. $e->getMessage ());

	echo json_encode ($array);
}
