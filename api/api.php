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
	
	$_uri = explode ('/', $_GET['uri']);
	
	if ($_uri [0] != 'register')
		$_auth->authenticate ();
	else
		$_auth->authenticateForRegister ();
	
	if (isset ($_GET['language']) && trim ($_GET['language']) != '')
		Localization::singleton ()->setLanguage ($_GET['language']);
	
	switch ($_uri [0])
	{
		case 'auth':
			
			require $corePath .'api/auth.php';
			
			break;
		
		case 'register':
			
			require $corePath .'api/register.php';
			
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
		
		default:
			
			if (!Business::singleton ()->sectionExists ($_uri [0]))
				throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);
			
			$_section = Business::singleton ()->getSection ($_uri [0]);
			
			$_service = str_replace ('..', '', trim (@$_uri [1]));
			
			if (!is_object ($_section) || $_service == '')
				throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);
			
			$_action = $_section->getAction (Action::TAPI);
			
			Business::singleton ()->setCurrent ($_section, $_action);
			
			foreach (Instance::singleton ()->getTypes () as $type => $path)
				require_once $path . $type .'.php';
			
			if (file_exists ($_section->getCompPath () .'_class.php'))
				include $_section->getCompPath () .'_class.php';
			
			if (file_exists ($_section->getCompPath () .'_function.php'))
				include $_section->getCompPath () .'_function.php';
			
			$file = $_section->getComponentPath () .'_api'. DIRECTORY_SEPARATOR . $_service .'.php';
			
			if (!file_exists ($file))
				throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid URI!');
			
			require $file;
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
	
	$array = array ('ERROR' => 'DATABASE_ERROR',
					'MESSAGE' => __ ('Database error! Please, contact administrator.'),
					'TECHNICAL' => $e->getMessage ());
	
	echo json_encode ($array);
}
catch (Exception $e)
{
	toLog ($e->getMessage ());
	
	header ('HTTP/1.1 '. ApiException::INTERNAL_SERVER_ERROR .' '. ApiException::$status [ApiException::INTERNAL_SERVER_ERROR]);
	header ('Content-Type: application/json');
	
	$array = array ('ERROR' => 'SYSTEM_ERROR',
					'MESSAGE' => 'System error! Please, contact administrator.',
					'TECHNICAL' => $e->getMessage ());
	
	echo json_encode ($array);
}