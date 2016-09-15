<?php
$primary = $field->getPrimary ();

if (empty ($primary))
	$primary = array_shift (Database::getPrimaryColumn ($field->getTable ()));

return $primary ." IN (SELECT ". $field->getRelationLink () ." FROM ". $field->getRelation () ." WHERE ". $field->getColumn () ." IN ('". implode ("', '", $field->getValue ()) ."'))";
?>