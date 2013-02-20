<?
return $field->getColumn () ." BIT(1) DEFAULT ". Database::toValue ($field) ." NOT NULL";
?>