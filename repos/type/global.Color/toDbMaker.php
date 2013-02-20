<?
return $field->getColumn () ." CHAR(6)". ($field->isEmpty () ? ($field->isRequired () ? " NOT NULL" : "")  : " DEFAULT ". Database::toValue ($field) ." NOT NULL") . ($field->isUnique () ? " UNIQUE" : "");
?>