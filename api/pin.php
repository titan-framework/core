<?php

if (Api::getHttpRequestMethod () != Api::POST && Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

$types = [ 'add','del' ];

if (!isset ($_uri [1]) || !in_array ($_uri [1], $types))
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

if (!$_auth->hasContext ('USER', 'USER-BY-MAIL', 'CLIENT-AS-USER', 'USER-BROWSER'))
	throw new ApiException (__ ('This application does not support user authentication!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to use unique e-mail as login.');

if (!isset ($_POST ['email']) || trim ($_POST ['email']) == '' || !preg_match('/^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/', $_POST ['email']))
	throw new ApiException (__ ('Invalid or empty e-mail!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$email = trim ($_POST ['email']);

$pin = 100000 + rand(0, 900000);

$type = strtoupper ($_uri [1]);

$db = Database::singleton ();

$sth = $db->prepare ("INSERT INTO _pin (_email, _pin, _type) VALUES (:email, :pin, :type)");

$sth->bindParam (':email', $email, PDO::PARAM_STR, 512);
$sth->bindParam (':pin', $pin, PDO::PARAM_STR, 6);
$sth->bindParam (':type', $type, PDO::PARAM_INT, 3);

$sth->execute ();

$mail = Mail::singleton ();

$subject = $mail->getTemplate ('pin-'. $_uri [1], 'subject');

$message = $mail->getTemplate ('pin-'. $_uri [1], 'text');

$instance = Instance::singleton ();

$app = html_entity_decode ($instance->getName (), ENT_QUOTES, 'UTF-8');

$search  = array ('[APP]', '[NAME]', '[URL]', '[PIN]');
$replace = array ($app, $app, $instance->getUrl (), $pin);

$subject = str_replace ($search, $replace, $subject);
$message = str_replace ($search, $replace, $message);

$headers  = "From: ". $instance->getName () ." <". $instance->getEmail () .">\r\n";
$headers .= "Reply-To: ". $instance->getEmail () ."\r\n";
$headers .= "Content-Type: text/html; charset=utf-8";

$flag = mail ($email, '=?utf-8?B?'. base64_encode ($subject) .'?=', $message, $headers);

if (!$flag)
	throw new ApiException (__ ('The e-mail cannot be sent. Maybe the server has problems at moment! Please, try again more later.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE);
