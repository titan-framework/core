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

$db->exec ("DELETE FROM _pin WHERE _type = 'ADD' AND _date < NOW() - INTERVAL '24 HOURS'");

$sth = $db->prepare ("SELECT _email FROM _pin WHERE _email = :email AND _pin = :pin AND _type = 'ADD' and _date >= NOW() - INTERVAL '24 HOURS'");

$sth->bindParam (':email', $email, PDO::PARAM_STR, 512);
$sth->bindParam (':pin', $pin, PDO::PARAM_STR, 6);

$sth->execute ();

if (!$sth->fetch (PDO::FETCH_OBJ))
    throw new ApiException (__ ('Expired or not registered PIN!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);

$userType = $_auth->getRegisterType ();

$sth = $db->prepare ("SELECT _email FROM _user WHERE _email = :email AND _type = :type AND _active = B'1' AND _deleted = B'0'");

$sth->bindParam (':email', $email, PDO::PARAM_STR, 512);
$sth->bindParam (':type', $userType->getName (), PDO::PARAM_STR, 6);

$sth->execute ();

if (!$sth->fetch (PDO::FETCH_OBJ))
{
	$id = Database::nextId ('_user', '_id');

    $login = $aux = array_shift (explode ('@', $email));

    $count = 1;

    while (TRUE)
    {
        $query = $db->query ("SELECT COUNT(*) AS n FROM _user WHERE _login ILIKE '". $login ."'");

        if (!(int) $query->fetch (PDO::FETCH_COLUMN))
            break;

        $login = $aux . $count++;
    }

	$fields = [
		'_id'		=> array ($id, PDO::PARAM_INT),
		'_login' 	=> array ($login, PDO::PARAM_STR),
		'_name'		=> array ('', PDO::PARAM_STR),
		'_email'	=> array ($email, PDO::PARAM_STR),
		'_password' => array (randomHash (13) .'_INVALID_HASH_'. randomHash (13), PDO::PARAM_STR),
		'_active'	=> array ('1', PDO::PARAM_STR),
		'_deleted'	=> array ('0', PDO::PARAM_STR),
		'_type'		=> array ($userType->getName (), PDO::PARAM_STR)
	];

	try
	{
		$db->beginTransaction ();

		$sql = "INSERT INTO _user (". implode (", ", array_keys ($fields)) .") VALUES (:". implode (", :", array_keys ($fields)) .")";

		$sth = $db->prepare ($sql);

		foreach ($fields as $key => $array)
			if (sizeof ($array) > 1)
				$sth->bindParam (':'. $key, $array [0], $array [1]);
			else
				$sth->bindParam (':'. $key, $array [0]);

		$sth->execute ();

		$sql = "SELECT _group FROM _type_group WHERE _type = :type";

		$sth = $db->prepare ($sql);

		$sth->bindParam (':type', $userType->getName (), PDO::PARAM_STR);

		$sth->execute ();

		$sthUser = $db->prepare ("INSERT INTO _user_group (_user, _group) VALUES (:user, :group)");

		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			$sthUser->execute (array (':user' => $id, ':group' => $obj->_group));

		$db->commit ();
	}
	catch (PDOException $e)
	{
		$db->rollBack ();

		throw new ApiException (__ ('Impossible to create user!'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, $e->getMessage () .' | '. print_r ($fields, TRUE));
	}
}

$jwt = $_auth->encrypt ([ 'email' => $email ]);

echo $jwt;
