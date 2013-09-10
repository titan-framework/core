<?php

if (Api::getHttpRequestMethod () != Api::GET || !$_auth->hasContext ('USER', 'USER-BY-ID', 'USER-BY-MAIL', 'CLIENT-AS-USER'))
	exit ();

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$sth = Database::singleton ()->prepare ("SELECT
											_id AS id, 
											_login AS login, 
											_name AS name, 
											_email AS mail,
											_type AS type,
											_language AS language,
											_timezone AS timezone
										FROM _user WHERE _id = :id LIMIT 1");

$sth->bindParam (':id', $user, PDO::PARAM_INT);

$sth->execute ();

header ('Content-Type: application/json');

echo json_encode ($sth->fetch (PDO::FETCH_OBJ));