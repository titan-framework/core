<?
$values = $field->getValue ();

if (!sizeof ($values))
	return '';

array_walk ($values, array ('Multiply', 'cleanValues'));

$sth = $db->prepare ("SELECT ". implode (", ", $field->getColumnsView ()) ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." IN ('". implode ("', '", $values) ."')");

$sth->execute ();

$output = array ();
while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$output [] = $field->makeView ($obj);

if (!sizeof ($output))
	return '';

return implode (', ', $output);
?>