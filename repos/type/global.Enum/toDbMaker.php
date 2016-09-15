<?php
$options = $field->getMapping ();

$max = 1;
foreach ($options as $trash => $value)
	if (strlen ($value) > $max)
		$max = strlen ($value);

return $field->getColumn () ." CHAR(". $max .") ". ($field->isEmpty () ? ($field->isRequired () ? "NOT NULL" : "DEFAULT NULL")  : "DEFAULT ". Database::toValue ($field) ." NOT NULL") . ($field->isUnique () ? " UNIQUE" : "");
?>