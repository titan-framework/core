<?php

if (is_null ($value) || (is_numeric ($value) && (int) $value === 0) || (is_string ($value) && trim ($value) == ''))
	return NULL;

$code = trim ($value);

$db = Database::singleton ();

$sth = $db->prepare ("SELECT _id FROM _cloud WHERE _code = :code");

$sth->bindParam (':code', $code, PDO::PARAM_STR);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if ($obj)
	return $obj->_id;

$user = (int) Api::singleton ()->getActiveApp ()->getUser ();

$id = Database::nextId ('_cloud', '_id');

$sth = $db->prepare ("INSERT INTO _cloud (_id, _code, _user) VALUES (:id, :code, :user)");

$sth->bindParam (':id', $id, PDO::PARAM_INT);
$sth->bindParam (':code', $code, PDO::PARAM_STR);
$sth->bindParam (':user', $user, PDO::PARAM_INT);

$sth->execute ();

return $id;