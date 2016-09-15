<?php
if ($field->isEmpty ())
	return '';

$linkColumn = $field->getLinkColumn ();
$linkView = $field->getLinkView ();
$fatherColumn = $field->getFatherColumn ();

$columns = implode (", ", $field->getColumnsView ());

$id = $field->getValue ();

$array = array ();

$pk = $field->getLinkColumn ();

while (!is_null ($id))
{
	$sth = $db->prepare ("SELECT ". $columns .", ". $pk .", ". $field->getFatherColumn () ." FROM ". $field->getLink () ." WHERE ". $pk ." = '". $id ."'");
	
	$sth->execute ();
	
	$item = $sth->fetch (PDO::FETCH_OBJ);
	
	if ($field->onApiAsPlainText ())
		$array [] = $field->makeView ($item);
	else
		$array [] = (object) array ('id' => $item->$pk, 'value' => $field->makeView ($item));
	
	$id = $item->$fatherColumn;
}

if ($field->onApiAsPlainText ())
	return implode (" > ", array_reverse ($array));
else
	return array_reverse ($array);
?>