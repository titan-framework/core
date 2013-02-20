<?
if ($value)			
	$field->setValue (explode (':', $value));
else
	$field->setValue (array (0, 0, 0));

return $field;
?>