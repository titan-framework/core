<? return $field->getTable () .'.'. $field->getColumn () ." LIKE '%". addslashes ($field->getValue ()) ."%'" ?>