<?php

if (Api::getHttpRequestMethod () != Api::DELETE)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [2]) || trim ($_uri [2]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$code = trim ($_uri [2]);

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$db = Database::singleton ();

try
{
	$db->beginTransaction ();
	
	$sth = $db->prepare ("UPDATE _cloud SET _deleted = B'1', _user = :user, _update = NOW(), _change = NOW() WHERE _id IN (SELECT m._file FROM _note_media m JOIN _note n ON n._id = m._note WHERE n._code = :code)");
	
	$sth->bindParam (':code', $code, PDO::PARAM_STR);
	$sth->bindParam (':user', $user, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$sth = $db->prepare ("UPDATE _note_media m SET _deleted = B'1', _user = :user, _update = NOW() FROM _note n WHERE n._id = m._note AND n._code = :code");
	
	$sth->bindParam (':code', $code, PDO::PARAM_STR);
	$sth->bindParam (':user', $user, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$sth = $db->prepare ("UPDATE _note SET _deleted = B'1', _user = :user, _update = NOW(), _change = NOW() WHERE _code = :code");
	
	$sth->bindParam (':code', $code, PDO::PARAM_STR);
	$sth->bindParam (':user', $user, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$db->commit ();
}
catch (PDOException $e)
{
	$db->rollBack ();
	
	throw $e;
}