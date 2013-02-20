<?
return $field->getTable () .'.'. $field->getColumn () .' AS order_time_'. $field->getColumn () .', to_char('. $field->getTable () .'.'. $field->getColumn () .', \'HH24:MI:SS\') AS '. $field->getColumn ();
?>