<?
if ($field->isEmpty ())
	return NULL;

if ($field->getLinkColumn () == $field->getLinkApi ())
	return $value;

$sth = Database::singleton ()->prepare ("SELECT ". $field->getLinkColumn () ." FROM ". $field->getLink () ." WHERE ". $field->getLinkApi () ." = :value");

$value = $field->getValue ();

$sth->execute (array (':value' => $value));

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	return NULL;

$column = $field->getLinkColumn ();

return $obj->$column;
?>