<?php
return $field->getColumn () ." NUMERIC(16,". $field->getPrecision () .") ". ($field->isEmpty () ? ($field->isRequired () ? "NOT NULL" : "DEFAULT NULL")  : "DEFAULT ". Database::toValue ($field) ." NOT NULL") . ($field->isUnique () ? " UNIQUE" : "");
?>
