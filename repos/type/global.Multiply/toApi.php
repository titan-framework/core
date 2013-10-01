<?
if (!isset ($itemId))
	return '';

$sql = "SELECT l.". implode (", l.", $field->getColumnsView ()) ." FROM ". $field->getRelation () ." r INNER JOIN ". $field->getLink () ." l ON r.". array_pop (explode ('.', $field->getLink ())) ." = l.". $field->getLinkColumn () ." WHERE r.". $field->getRelationLink () ." = '". $itemId ."'";

$sth = $db->prepare ($sql);

$sth->execute ();

$output = array ();

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$output [] = str_replace (';', ',', $field->makeView ($obj));

if (!sizeof ($output))
	return '';

return implode (';', $output);
?>