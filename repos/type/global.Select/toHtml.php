<?php
if ($field->isEmpty ())
	return '&nbsp;';

$sth = $db->prepare ("SELECT ". implode (", ", $field->getColumnsView ()) ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = :value");

$sth->bindParam (':value', $field->getValue ());

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	return '&nbsp;';

return $field->makeView ($obj);
?>