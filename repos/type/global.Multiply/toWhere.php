<?
$primary = $field->getPrimary ();

if (empty ($primary))
	$primary = array_shift (Database::getPrimaryColumn ($field->getTable ()));

return $primary ." IN (SELECT ". array_pop (explode ('.', $field->getTable ())) ." FROM ". $field->getRelation () ." WHERE ". $field->getColumn () ." IN ('". implode ("', '", $field->getValue ()) ."'))";
?>