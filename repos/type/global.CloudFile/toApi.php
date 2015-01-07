<?php

if ($field->isEmpty ())
	return NULL;

$sth = Database::singleton ()->prepare ("SELECT _code FROM _cloud WHERE _id = :id");

$sth->bindParam (':id', $field->getValue (), PDO::PARAM_INT);

$sth->execute ();

return $sth->fetchColumn ();