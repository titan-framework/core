<?
try
{
	if (!isset ($_GET ['photoId']) || !$_GET['photoId'])
		throw new Exception (__ ('Error! Data losted.'));

	$photoId = $_GET['photoId'];

	$db = Database::singleton ();

	$sth = $db->prepare ("SELECT _name, _size, _mimetype FROM _media WHERE _id = ". $photoId);

	$sth->execute ();

	$obj = $sth->fetch (PDO::FETCH_OBJ);

	if (!$obj)
		throw new Exception (__ ('The photo [ [1] ] does not exist into DB!', $photoId));

	header ('Content-Type: '. $obj->_mimetype);
	header ('Content-Disposition: inline; filename=' . fileName ($obj->_name));

	$path = Archive::singleton ()->getDataPath () . 'photo_' . str_pad ($photoId, 7, '0', STR_PAD_LEFT);

	if (!file_exists ($path))
		throw new Exception (__ ('The photo [ [1] ] does not exist into DB!', $photoId));

	$binary = fopen ($path, 'rb');

	$buffer = fread ($binary, filesize ($path));

	fclose ($binary);

	echo $buffer;

	exit ();
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
}
catch (Exception $e)
{
	toLog ($e->getMessage ());
}
?>