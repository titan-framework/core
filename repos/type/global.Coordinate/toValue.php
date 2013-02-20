<?
$value = $field->getValue ();

if (sizeof ($value) != 3)
	return "NULL";

return "'". implode (',', $value) ."'";
?>