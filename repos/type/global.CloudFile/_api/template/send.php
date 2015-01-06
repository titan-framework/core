<?php

if (!isset ($_uri [2]) || trim ($_uri [2]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$code = trim ($_uri [2]);

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$db = Database::singleton ();

$sth = $db->prepare ("SELECT * FROM _cloud WHERE _code = :code");

$sth->bindParam (':code', $code, PDO::PARAM_STR);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if ($obj)
{
	if ((int) $obj->_ready || (int) $obj->_deleted)
		exit ();
	
	if ((int) $obj->_user != $user)
		throw new ApiException (__ ('You can not access this file!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);
}

$required = array ('creation_date', 'last_change');

foreach ($required as $trash => $column)
	if (!array_key_exists ($column, $_POST) || !(int) $_POST [$column])
		throw new ApiException (__ ('Required field [[1]] is missing or empty!', $column), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$creation = date ('j-n-Y H:i:s', (int) $_POST ['creation_date']);
$last = date ('j-n-Y H:i:s', (int) $_POST ['last_change']);

if (!isset ($_FILES ['file']) || !is_array ($_FILES ['file']) || !array_key_exists ('size', $_FILES ['file']) || !(int) $_FILES ['file']['size'])
	throw new ApiException (__ ('Required field [[1]] is missing or empty!', 'file'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$archive = Archive::singleton ();

$temp = $_FILES ['file']['tmp_name'];

$size = (int) $_FILES ['file']['size'];

$name = fileName ($_FILES ['file']['name']);

if (array_key_exists ('mime_type', $_POST) && trim ($_POST ['mime_type']) != '')
	$mime = trim ($_POST ['mime_type']);
else
{
	$mime = $_FILES ['file']['type'];
	
	$genericMimes = array ('application/octet-stream', 'binary');
	
	if (in_array ($mime, $genericMimes))
		$mime = $archive->getMimeByExtension (array_pop (explode ('.', $_FILES ['file']['name'])));
}

if (!$archive->isAcceptable ($mime))
	throw new ApiException (__ ('This type of file is not accepted by the system ( [1] ) !', $_mime), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

try
{
	$db->beginTransaction ();
	
	if (!$obj)
	{
		$id = Database::nextId ('_cloud', '_id');
		
		$sql = "INSERT INTO _cloud (_id, _code, _name, _mimetype, _size, _user, _creation_date, _last_change, _ready) VALUES (:id, :code, :name, :mime, :size, :user, :creation, :last, B'1')";
		
		$sth = $db->prepare ($sql);
		
		$sth->bindParam (':id', $id, PDO::PARAM_INT);
		$sth->bindParam (':code', $code, PDO::PARAM_STR);
		$sth->bindParam (':name', $name, PDO::PARAM_STR, 256);
		$sth->bindParam (':mime', $mime, PDO::PARAM_STR, 256);
		$sth->bindParam (':size', $size, PDO::PARAM_INT);
		$sth->bindParam (':user', $user, PDO::PARAM_INT);
		$sth->bindParam (':creation', $creation, PDO::PARAM_STR);
		$sth->bindParam (':last', $last, PDO::PARAM_STR);
	}
	else
	{
		$id = $obj->_id;
		
		$sql = "UPDATE _cloud SET _name = :name, _mimetype = :mime, _size = :size, _creation_date = :creation, _last_change = :last, _update = now(), _ready = B'1' WHERE _id = :id";
		
		$sth = $db->prepare ($sql);
		
		$sth->bindParam (':id', $id, PDO::PARAM_INT);
		$sth->bindParam (':name', $name, PDO::PARAM_STR, 256);
		$sth->bindParam (':mime', $mime, PDO::PARAM_STR, 256);
		$sth->bindParam (':size', $size, PDO::PARAM_INT);
		$sth->bindParam (':creation', $creation, PDO::PARAM_STR);
		$sth->bindParam (':last', $last, PDO::PARAM_STR);
	}
	
	$sth->execute ();
	
	$file = realpath ($archive->getDataPath ()) . DIRECTORY_SEPARATOR .'cloud_'. str_pad ($id, 7, '0', STR_PAD_LEFT);
	
	if (!move_uploaded_file ($temp, $file))
		if (!rename ($temp, $file))
			throw new Exception (__ ('Unable copy file to directory [ [1] ]!', $temp .' > '. $file));
	
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