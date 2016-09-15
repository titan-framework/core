<?php
return $field->getTable () .'.'. $field->getColumn () .' AS order_date_'. $field->getColumn () .', STRFTIME(\'%d-%m-%Y'. ($field->showTime () ? ' %H:%M:%S' : '') .'\', '. $field->getTable () .'.'. $field->getColumn () .', \'localtime\') AS '. $field->getColumn ();
?>