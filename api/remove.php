<?php

if (Api::getHttpRequestMethod () != Api::POST && Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!$_auth->hasContext ('USER', 'USER-BY-MAIL', 'CLIENT-AS-USER', 'USER-BROWSER'))
	throw new ApiException (__ ('This application does not support user authentication!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to use unique e-mail as login.');

if (!isset ($_POST ['email']) || trim ($_POST ['email']) == '' || !preg_match('/^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/', $_POST ['email']))
	throw new ApiException (__ ('Invalid or empty e-mail!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

if (!isset ($_POST ['pin']) || strlen (preg_replace ('/[^0-9]/i', '', $_POST ['pin'])) != 6)
	throw new ApiException (__ ('Invalid or empty PIN!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$email = $_POST ['email'];
$pin = preg_replace ('/[^0-9]/i', '', $_POST ['pin']);

$db = Database::singleton ();

$db->exec ("DELETE FROM _pin WHERE _type = 'DEL' AND _date < NOW() - INTERVAL '60 MINUTES'");

$sth = $db->prepare ("SELECT _email FROM _pin WHERE _email = :email AND _pin = :pin AND _type = 'DEL' and _date >= NOW() - INTERVAL '60 MINUTES'");

$sth->bindParam (':email', $email, PDO::PARAM_STR, 512);
$sth->bindParam (':pin', $pin, PDO::PARAM_STR, 6);

$sth->execute ();

if (!$sth->fetch (PDO::FETCH_OBJ))
    throw new ApiException (__ ('Expired or not registered PIN!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);

$sth = $db->prepare ("DELETE FROM _user WHERE _email = :email");

$sth->execute ([ ':email' => $email ]);
