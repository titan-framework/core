<?php
try
{
	set_error_handler ('apiPhpError', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
	
	if (isset ($_GET['language']) && trim ($_GET['language']) != '')
		Localization::singleton ()->setLanguage ($_GET['language']);
	
	switch (@$_GET ['function'])
	{
		case 'auth':
			
			require $corePath .'api/auth.php';
			
			break;
		
		case 'alerts':
			
			require $corePath .'api/alerts.php';
			
			break;
		
		default:
			throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid URI!');
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