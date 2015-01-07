<?php

if (Api::getHttpRequestMethod () != Api::DELETE)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [2]) || trim ($_uri [2]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$code = trim ($_uri [2]);

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$sth = Database::singleton ()->prepare ("UPDATE _cloud SET _deleted = B'1' WHERE _code = :code AND _user = :user");

$sth->bindParam (':code', $code, PDO::PARAM_STR);
$sth->bindParam (':user', $user, PDO::PARAM_INT);

$sth->execute ();
