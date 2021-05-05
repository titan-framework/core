<?php
if ($field->isEmpty ())
	return '';

if ($field->getLinkColumn () == $field->getLinkApi ())
	return $field->getValue ();

$sth = $db->prepare ("SELECT ". $field->getLinkApi () ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = :value");

$value = $field->getValue ();

$sth->bindParam (':value', $value);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	return '';

$column = $field->getLinkApi ();

return $obj->$column;
?>