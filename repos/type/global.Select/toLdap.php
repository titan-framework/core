<?php
if ($field->isEmpty ())
	return '';

$sth = $db->prepare ("SELECT ". implode (", ", $field->getColumnsView ()) ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = :value");

$sth->bindParam (':value', $field->getValue (), $field->getBindType ());

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	return '';

return removeAccents ($field->makeView ($obj));
?>