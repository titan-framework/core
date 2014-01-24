<?
if ($field->isEmpty ())
	return '';

if ($field->getLinkColumn () == $field->getLinkApi ())
	return $value;

$sth = $db->prepare ("SELECT ". $field->getLinkApi () ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = :value");

$value = $field->getValue ();
$bind  = $field->getBindType ();

$sth->bindParam (':value', $value, $bind);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	return '';

$column = $field->getLinkApi ();

return $obj->$column;
?>