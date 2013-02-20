<?
if (is_null ($field->getValue ()) || !(int) $field->getValue ())
	return '';

$db = Database::singleton ();

$sth = $db->prepare ("SELECT _name, _size FROM _file WHERE _id = '". $field->getValue () ."'");

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	return '';

return $obj->_name .' ('. number_format ($obj->_size, 0, '', '.') .' KBytes)';