<?php

if (!$_auth->hasContext ('USER', 'USER-BY-ID', 'USER-BY-MAIL', 'CLIENT-AS-USER', 'USER-BROWSER'))
	throw new ApiException (__ ('This application does not support user authentication!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException (__ ('Invalid user!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$db = Database::singleton ();

switch (Api::getHttpRequestMethod ())
{
	case Api::GET:

		// GET /api/user

		$sth = $db->prepare (
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

		break;

	case Api::DELETE:

		// DELETE /api/user/[pin]

		if (!isset ($_uri [1]) || strlen (preg_replace ('/[^0-9]/i', '', $_uri [1])) != 6)
			throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

		$pin = preg_replace ('/[^0-9]/i', '', $_uri [1]);

		$db->exec ("DELETE FROM _pin WHERE _type = 'DEL' AND _date < NOW() - INTERVAL '60 MINUTES'");

		$sth = $db->prepare ("SELECT u._id, u._email FROM _pin AS p JOIN _user AS u ON u._email = p._email WHERE u._id = :user AND p._pin = :pin AND p._type = 'DEL' AND p._date >= NOW() - INTERVAL '60 MINUTES'");

		$sth->bindParam (':user', $user, PDO::PARAM_INT);
		$sth->bindParam (':pin', $pin, PDO::PARAM_STR, 6);

		$sth->execute ();

		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if (!$obj || !isset ($obj->_id) || is_null ($obj->_id) || !(int) $obj->_id || (int) $obj->_id !== (int) $user)
			throw new ApiException (__ ('Expired or not registered PIN!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);

		$sth = $db->prepare ("DELETE FROM _user WHERE _id = :user");

		$sth->execute ([ ':user' => (int) $obj->_id ]);

		try
		{
			$sth = $db->prepare ("DELETE FROM _pin WHERE _email = :email");

			$sth->execute ([ ':email' => $obj->_email ]);
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
		}

		break;
	
	case Api::POST:
	case Api::PUT:

		if (!isset ($_POST ['name']) || trim ($_POST ['name']) == '')
			throw new ApiException (__ ('Invalid or empty name!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);
		
		$sth = $db->prepare ("UPDATE _user SET _name = :name, _update_date = now() WHERE _id = :user");

		$sth->bindParam (':user', $user, PDO::PARAM_INT);
		$sth->bindParam (':name', $_POST ['name'], PDO::PARAM_STR);

		$sth->execute ();

		break;

	default:
		throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);
}
