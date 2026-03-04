<?php
if ($field->isEmpty ())
	return '&nbsp;';

$linkColumn = $field->getLinkColumn ();
$linkView = $field->getLinkView ();
$fatherColumn = $field->getFatherColumn ();

$columns = implode (", ", $field->getColumnsView ());

$id = $field->getValue ();

$array = array ();
$visited = array ();

while (!is_null ($id))
{
	if (isset ($visited [$id]))
		break;

	$visited [$id] = TRUE;

	$sth = $db->prepare ("SELECT ". $columns .", ". $field->getLinkColumn () .", ". $field->getFatherColumn () ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = '". $id ."'");

	$sth->execute ();

	$item = $sth->fetch (PDO::FETCH_OBJ);

	if (!$item)
		break;

	$array [] = $field->makeView ($item);

	$id = $item->$fatherColumn;
}

return implode (" &raquo; ", array_reverse ($array));
?>