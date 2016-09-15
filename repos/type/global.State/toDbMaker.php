<?php
return $field->getColumn () ." CHAR(2) ". ($field->isEmpty () ? ($field->isRequired () ? "NOT NULL" : "DEFAULT NULL")  : "DEFAULT ". Database::toValue ($field) ." NOT NULL") . ($field->isUnique () ? " UNIQUE" : "");
?>