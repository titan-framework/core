<?php
return $field->getColumn () ." VARCHAR(". ((int) $field->getMaxLength () ? $field->getMaxLength () : "256") .")". ($field->isEmpty () ? ($field->isRequired () ? " NOT NULL" : "")  : " DEFAULT ". Database::toValue ($field) ." NOT NULL") . ($field->isUnique () ? " UNIQUE" : "");
?>