<?php
$values = $field->getValue ();

array_unshift ($values, 0);

$sth = $db->prepare ("SELECT ". implode (", ", $field->getColumnsView ()) ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." IN ('". implode ("', '", $values) ."')");

$sth->execute ();

$output = array ();
while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$output [] = $field->makeView ($obj);

if (!sizeof ($output))
	return '&nbsp;';

return implode (', ', $output);
?>