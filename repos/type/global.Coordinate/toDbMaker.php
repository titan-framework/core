<?php
return $field->getColumn () ." VARCHAR(32) ". ($field->isEmpty () ? ($field->isRequired () ? "NOT NULL" : "DEFAULT NULL")  : "DEFAULT ". Database::toValue ($field) ." NOT NULL") . ($field->isUnique () ? " UNIQUE" : "");
?>