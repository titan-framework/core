<?
return $field->getColumn () ." TEXT". ($field->isEmpty () ? ($field->isRequired () ? " NOT NULL" : "")  : " DEFAULT ". Database::toValue ($field) ." NOT NULL") . ($field->isUnique () ? " UNIQUE" : "");
?>