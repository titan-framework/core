<?
return $field->getTable () .'.'. $field->getColumn () .' AS order_date_'. $field->getColumn () .', to_char('. $field->getTable () .'.'. $field->getColumn () .', \'DD-MM-YYYY'. ($field->showTime () ? ' HH24:MI:SS' : '') .'\') AS '. $field->getColumn ();
?>