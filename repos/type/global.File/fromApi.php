<?php

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

$genericMimes = array ('application/octet-stream', 'binary');

$db = Database::singleton ();

try
{
	$db->beginTransaction ();
	
	if (in_array ($fileType, $genericMimes))
		$fileType = $archive->getMimeByExtension (array_pop (explode ('.', $value ['name'])));

	if (!$archive->isAcceptable ($fileType) || !$field->isAcceptable ($fileType))
		throw new Exception (__ ('This type of file is not accepted by the system ( [1] ) !', $fileType));
	
	$id = Database::nextId ('_file', '_id');
	
	$array = array (
		array ('_id', $id, PDO::PARAM_INT),
		array ('_name', $fileName, PDO::PARAM_STR),
		array ('_mimetype', $fileType, PDO::PARAM_STR),
		array ('_size', $fileSize, PDO::PARAM_INT),
		array ('_user', $user, PDO::PARAM_INT)
	);
	
	if (!$field->isPublic ())
	{
		$hash = File::getRandomHash ();
		
		$array [] = array ('_public', 0, PDO::PARAM_INT);
		$array [] = array ('_hash', $hash, PDO::PARAM_STR);
	}
	
	$columns = array ();
	$values  = array ();
	
	foreach ($array as $trash => $item)
	{
		$columns [] = $item [0];
		$values []  = ':'. $item [0];
	}
	
	$sth = $db->prepare ("INSERT INTO _file (". implode (", ", $columns) .") VALUES (". implode (", ", $values) .")");
	
	foreach ($array as $trash => $item)
		$sth->bindParam (':'. $item [0], $item [1], $item [2]);
	
	$sth->execute ();
	
	$file = realpath (File::getFilePath ());

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