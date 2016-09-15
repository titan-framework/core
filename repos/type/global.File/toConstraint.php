<?php
$db = Database::singleton ();

$table = explode ('.', $field->getTable ());

$table = sizeof ($table) > 1 ? $table : array ($db->getSchema (), $table [0]);

return "CONSTRAINT ". $table [1] ."_". $field->getColumn () ."_file_fk FOREIGN KEY (". $field->getColumn () .") REFERENCES ". $db->getSchema () ."._file(_id) ON DELETE RESTRICT ON UPDATE CASCADE NOT DEFERRABLE";
?>