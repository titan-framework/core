<?php

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!$_auth->hasContext ('USER', 'USER-BY-ID', 'USER-BY-MAIL', 'CLIENT-AS-USER', 'USER-BROWSER'))
	throw new ApiException (__ ('This application does not support user authentication!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException (__ ('Invalid user!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$sth = Database::singleton ()->prepare (
	"SELECT
		_id AS id,
		_login AS login,
		_name AS name,
		_email AS mail,
		_type AS type,
		_language AS language,
		_timezone AS timezone
	FROM _user WHERE _id = :id AND _active = B'1' AND _deleted = B'0' LIMIT 1"
);

$sth->bindParam (':id', $user, PDO::PARAM_INT);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!is_object ($obj))
	throw new ApiException (__ ('User does not exist or is inactive!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);

Log::singleton ()->add ('LOGON', "User authenticated on API REST using application '". Api::singleton ()->getActiveApp ()->getName () ."'.", Log::SECURITY, FALSE, TRUE);

header ('Content-Type: application/json');

echo json_encode ($obj);
