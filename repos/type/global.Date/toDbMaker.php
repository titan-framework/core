<?
return $field->getColumn () ." TIMESTAMP WITHOUT TIME ZONE ". ($field->isEmpty () ? ($field->isRequired () ? "DEFAULT NOW()" : "DEFAULT NULL")  : "DEFAULT ". Database::toSql ($field) ." NOT NULL") . ($field->isUnique () ? " UNIQUE" : "");
?>