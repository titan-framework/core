<?
$db = Database::singleton ();

$table = explode ('.', $field->getTable ());
$link  = explode ('.', $field->getLink ());

$table = sizeof ($table) > 1 ? $table : array ($db->getSchema (), $table [0]);
$link  = sizeof ($link) > 1  ? $link  : array ($db->getSchema (), $link [0]);

return "CONSTRAINT ". $table [1] ."_". $field->getColumn () ."_". $link [1] ."_fk FOREIGN KEY (". $field->getColumn () .") REFERENCES ". $link [0] .".". $link [1] ."(". $field->getLinkColumn () .") ON DELETE RESTRICT ON UPDATE CASCADE NOT DEFERRABLE";
?>