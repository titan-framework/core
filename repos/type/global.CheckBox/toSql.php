<?php
return "array_to_string (". $field->getTable () .".". $field->getColumn () .", ';', '') AS ". $field->getColumn ();
?>