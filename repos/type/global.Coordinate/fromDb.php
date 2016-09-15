<?php
if (!$value)
	$value = array ("0 0' 0''N", "0 0' 0''E", 0);
else
	$value = explode (',', $value);

$field->setValue ($value);

return $field;
?>