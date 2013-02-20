<?
$arrayFrom = array ();
$arrayItem = array ();
$arrayFor  = array ();

$form =& Form::singleton ('relation.xml');

$db = Database::singleton ();

$field = $form->getField ('_REL_FOR_');

$columns = implode (", ", $field->getColumnsView ());

$sth = $db->prepare ("SELECT ". $columns .", ". $field->getLinkColumn () ." FROM ". $field->getLink () . ($field->getWhere () != '' ? ' WHERE '. $field->getWhere () : '') ." ORDER BY ". $columns);

$sth->execute ();

$colPrimary = $field->getLinkColumn ();

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$arrayItem [$obj->$colPrimary] = $field->makeView ($obj);

$field = $form->getField ('_REL_FROM_');

$columns = implode (", ", $field->getColumnsView ());

$sql = "SELECT ". $columns .", ". $field->getLinkColumn () ." FROM ". $field->getLink () ." WHERE 1 = 1";

if ($itemId)
	$sql .= " AND ". $field->getLinkColumn () ." NOT IN (SELECT ". $field->getColumn () ." FROM ". $form->getTable () ." WHERE ". $form->getField ('_REL_FOR_')->getColumn () ." = '". $itemId ."')";

$aux = $field->getColumnsView ();

if (isset ($_POST['letter']) && trim ($_POST['letter']) != '' && sizeof ($aux))
	$sql .= " AND ". $aux [0] ." ILIKE '". $_POST['letter'] ."%'";

$sql .= " ORDER BY ". $columns;

$sth = $db->prepare ($sql);

$sth->execute ();

$colPrimary = $field->getLinkColumn ();

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$arrayFrom [$obj->$colPrimary] = $field->makeView ($obj);

if ($itemId)
{
	$table = $field->getLinkTable ();
	
	$relTable = $form->getTable ();
	
	$sth = $db->prepare ("	SELECT ". $table .".". $colPrimary .", ". $columns ." 
							FROM ". $table ." 
							LEFT JOIN ". $relTable ." ON ". $relTable .".". $field->getColumn () ." = ". $table .".". $field->getLinkColumn () ."
							WHERE ". $relTable .".". $form->getField ('_REL_FOR_')->getColumn () ." = '". $itemId ."' ORDER BY ". $columns);
	
	$sth->execute ();
	
	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		$arrayFor [$obj->$colPrimary] = $field->makeView ($obj);
}
?>