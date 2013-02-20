<?
if ($field->isEmpty ())
	return NULL;

$array = $field->getValue ();

return implode ('-', array_reverse ($array));
?>