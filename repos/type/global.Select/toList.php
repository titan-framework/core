<?
if ($field->isEmpty ())
	return '&nbsp;';

$columns = $field->getColumnsView ();

$color = trim ($field->getLinkColor ());

if ($color != '')
	$columns [] = $color;

$sth = $db->prepare ("SELECT ". implode (", ", $columns) ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = :value");

$sth->bindParam (':value', $field->getValue (), $field->getBindType ());

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	return '&nbsp;';

if ($color != '')
{
	$c = empty ($obj->$color) ? 'FFF' : $obj->$color;
	
	return '<label style="height: 15px; white-space: nowrap; background-color: #'. $c .'; border: #FFF 1px solid; padding: 2px 5px; font-size: 9px; font-weight: bold; color: #'. Color::contrast ($c) .'">'. $field->makeView ($obj) .'</label>';
}

return $field->makeView ($obj);
?>