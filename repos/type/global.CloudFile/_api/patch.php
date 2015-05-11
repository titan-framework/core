<?php

/*
 * Required fields: 'change'.
 * Optional fields: 'name' and 'deleted'.
 */

if (Api::getHttpRequestMethod () != Api::PATCH)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [2]) || trim ($_uri [2]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$code = trim ($_uri [2]);

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException (__ ('Invalid user!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

if (!array_key_exists ('change', $_POST) || !is_numeric ($_POST ['change']) || !(int) $_POST ['change'])
	throw new ApiException (__ ('Required field [[1]] is missing or empty!', 'change'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$change = date ('Y-n-j H:i:s', (int) $_POST ['change']);

$db = Database::singleton ();

$sth = $db->prepare ("SELECT * FROM _cloud WHERE _code = :code");

$sth->bindParam (':code', $code, PDO::PARAM_STR);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	throw new ApiException (__ ('This file is not available!'), ApiException::ERROR_RESOURCE_MISSING, ApiException::NOT_FOUND);

if (array_key_exists ('name', $_POST) && trim ($_POST ['name']) != '')
	$name = fileName ($_POST ['name']);
else
	$name = $obj->_name;

if (array_key_exists ('deleted', $_POST) && !is_null ($_POST ['deleted']) && trim ($_POST ['deleted']) != '')
	$del = (int) trim ($_POST ['deleted']);
else
	$del = (int) $obj->_deleted;

$deleted = $del ? "1" : "0";

$id = $obj->_id;

$sql = "UPDATE _cloud SET _name = :name, _user = :user, _change = :change, _deleted = :deleted, _update = NOW()
		WHERE _id = :id AND (_change IS NULL OR _change < :change)";

$sth = $db->prepare ($sql);

$sth->bindParam (':id', $id, PDO::PARAM_INT);
$sth->bindParam (':name', $name, PDO::PARAM_STR, 256);
$sth->bindParam (':user', $user, PDO::PARAM_INT);
$sth->bindParam (':change', $change, PDO::PARAM_STR);
$sth->bindParam (':deleted', $deleted, PDO::PARAM_STR);

$sth->execute ();