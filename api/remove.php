<?php

if (Api::getHttpRequestMethod () != Api::POST && Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!$_auth->hasContext ('USER', 'USER-BY-MAIL', 'CLIENT-AS-USER', 'USER-BROWSER'))
	throw new ApiException (__ ('This application does not support user authentication!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to use unique e-mail as login.');

if (!Database::isUnique ('_user', '_email'))
	throw new ApiException (__ ('e-Mail must be unique to authenticate user! Please, report to system administrator.'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

if (!isset ($_POST ['pin']) || strlen (preg_replace ('/[^0-9]/i', '', $_POST ['pin'])) != 6)
	throw new ApiException (__ ('Invalid or empty PIN!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$pin = preg_replace ('/[^0-9]/i', '', $_POST ['pin']);

$db = Database::singleton ();

$db->exec ("DELETE FROM _pin WHERE _type = 'DEL' AND _date < NOW() - INTERVAL '60 MINUTES'");

$sth = $db->prepare ("SELECT u._id FROM _pin AS p JOIN _user AS u ON u._email = p._email WHERE u._id = :user AND p._pin = :pin AND p._type = 'DEL' AND p._date >= NOW() - INTERVAL '60 MINUTES'");

$sth->bindParam (':user', $_auth->getUser (), PDO::PARAM_INT);
$sth->bindParam (':pin', $pin, PDO::PARAM_STR, 6);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj || !isset ($obj->_id) || is_null ($obj->_id) || !(int) $obj->_id)
    throw new ApiException (__ ('Expired or not registered PIN!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);

$sth = $db->prepare ("DELETE FROM _user WHERE _id = :user");

$sth->execute ([ ':user' => (int) $obj->_id ]);
