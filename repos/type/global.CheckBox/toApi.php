<?php
$map = $field->getMapping ();

$buffer = array ();

foreach ($field->getValue () as $trash => $value)
	if (array_key_exists ($value, $map))
		$buffer [] = str_replace (';', ',', $map [$value]);

return implode (';', $buffer);
?>