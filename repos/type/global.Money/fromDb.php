<?
if ($value)
	$field->setValue ((float) str_replace (array ('$', ','), '', $value));
else
	$field->setValue ((float) 0);

return $field;
?>