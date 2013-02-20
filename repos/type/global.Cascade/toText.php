<?
if ($field->isEmpty ())
	return '';

$linkColumn = $field->getLinkColumn ();
$linkView = $field->getLinkView ();
$fatherColumn = $field->getFatherColumn ();

$columns = implode (", ", $field->getColumnsView ());

$id = $field->getValue ();

$array = array ();

while (!is_null ($id))
{
	$sth = $db->prepare ("SELECT ". $columns .", ". $field->getLinkColumn () .", ". $field->getFatherColumn () ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = '". $id ."'");
	
	$sth->execute ();
	
	$item = $sth->fetch (PDO::FETCH_OBJ);
	
	$array [] = $field->makeView ($item);
	
	$id = $item->$fatherColumn;
}

return implode (" > ", array_reverse ($array));
?>