<?
if ($field->isEmpty ())
	return '';

$sth = $db->prepare ("SELECT ". implode (", ", $field->getColumnsView ()) ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = :value");

$value = $field->getValue ();
$bind  = $field->getBindType ();

$sth->bindParam (':value', $value, $bind);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	return '';

return $field->makeView ($obj);
?>