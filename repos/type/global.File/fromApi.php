<?
$archive = Archive::singleton ();

if (!isset ($value) || !is_array ($value) || !array_key_exists ('size', $value) || !(int) $value ['size'])
	return NULL;

$auth = Api::singleton ()->getActiveApp ();

if (is_null ($auth) || !is_object ($auth) || !is_numeric ($auth->getUser ()) || !(int) $auth->getUser ())
	$user = NULL;
else
	$user = (int) $auth->getUser ();

$fileTemp = $value ['tmp_name'];
$fileSize = $value ['size'];
$fileType = $value ['type'];
$fileName = fileName ($value ['name']);

$db = Database::singleton ();

try
{
	$db->beginTransaction ();
	
	if ($fileType == 'application/octet-stream')
		$fileType = $archive->getMimeByExtension (array_pop (explode ('.', $value ['name'])));

	if (!$archive->isAcceptable ($fileType) || !$field->isAcceptable ($fileType))
		throw new Exception (__ ('This type of file is not accepted by the system ( [1] ) !', $fileType));
	
	$id = Database::nextId ('_file', '_id');
	
	$sql = "INSERT INTO _file (_id, _name, _mimetype, _size, _user) VALUES (:id, :name, :mime, :size, :user)";
	
	// throw new Exception ($id .'#'. $fileName .'#'. $fileType .'#'. $fileSize .'#'. $user);
	
	$sth = $db->prepare ($sql);
	
	$sth->bindParam (':id', $id, PDO::PARAM_INT);
	$sth->bindParam (':name', $fileName, PDO::PARAM_STR, 256);
	$sth->bindParam (':mime', $fileType, PDO::PARAM_STR, 256);
	$sth->bindParam (':size', $fileSize, PDO::PARAM_INT);
	$sth->bindParam (':user', $user, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$file = realpath ($archive->getDataPath ()) . DIRECTORY_SEPARATOR .'file_'. str_pad ($id, 7, '0', STR_PAD_LEFT);

	if (is_uploaded_file ($fileTemp))
	{
		if (!move_uploaded_file ($fileTemp, $file))
			throw new Exception (__ ('Unable copy file to directory [ [1] ]!', $fileTemp .' > '. $file));
	}
	elseif (!rename ($fileTemp, $file))
		throw new Exception (__ ('Unable copy file to directory [ [1] ]!', $fileTemp .' > '. $file));
	
	Lucene::singleton ()->saveFile ($id);
	
	$db->commit ();
	
	return $id;
}
catch (PDOException $e)
{
	$db->rollBack ();
	
	throw $e;
}
catch (Exception $e)
{
	$db->rollBack ();
	
	throw $e;
}

return NULL;
?>