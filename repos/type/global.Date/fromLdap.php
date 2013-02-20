<?
if (strlen ($value) != 8)
	$field->setValue (array (0, 0, 0));
else
	$field->setValue (array (substr ($value, 6, 2), substr ($value, 4, 2), substr ($value, 0, 4)));

return $field;
?>