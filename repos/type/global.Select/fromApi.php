<?
if ($field->isEmpty ())
	return NULL;

if ($field->getLinkColumn () == $field->getLinkApi ())
	return $value;

$sth = $db->prepare ("SELECT ". $field->getLinkColumn () ." FROM ". $field->getLink () ." WHERE ". $field->getLinkApi () ." = :value");

$value = $field->getValue ();
$bind  = $field->getBindType ();

$sth->bindParam (':value', $value);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	return NULL;

$column = $field->getLinkColumn ();

return $obj->$column;
?>