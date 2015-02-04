<?php

/*
 * Required fields: 'file' and 'change'.
 * Optional fields: 'name', 'author', 'devise' and 'mimetype'.
 */

if (Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [2]) || trim ($_uri [2]) == '')
	throw new ApiException (__ ('Invalid URI!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$code = trim ($_uri [2]);

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException (__ ('Invalid user!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

if (!array_key_exists ('change', $_POST) || !is_numeric ($_POST ['change']) || !(int) $_POST ['change'])
	throw new ApiException (__ ('Required field [[1]] is missing or empty!', 'change'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$change = date ('Y-n-j H:i:s', (int) $_POST ['change']);

if (!array_key_exists ('file', $_FILES) || !is_array ($_FILES ['file']) || !array_key_exists ('size', $_FILES ['file']) || !(int) $_FILES ['file']['size'])
	throw new ApiException (__ ('Required field [[1]] is missing or empty!', 'file'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

if (array_key_exists ('author', $_POST) && is_numeric ($_POST ['author']) && (int) $_POST ['author'])
	$author = (int) $_POST ['author'];
else
	$author = $user;

if (array_key_exists ('devise', $_POST) && is_numeric ($_POST ['devise']) && (int) $_POST ['devise'])
	$devise = date ('Y-n-j H:i:s', (int) $_POST ['devise']);
else
	$devise = $change;

$db = Database::singleton ();

$sth = $db->prepare ("SELECT * FROM _cloud WHERE _code = :code");

$sth->bindParam (':code', $code, PDO::PARAM_STR);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if ($obj)
{
	if ((int) $obj->_user != $user)
		throw new ApiException (__ ('You do not have permission to modify this file!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);
	
	if ((int) $obj->_ready)
		exit ();
}

$archive = Archive::singleton ();

$temp = $_FILES ['file']['tmp_name'];

$size = (int) $_FILES ['file']['size'];

if (array_key_exists ('name', $_POST) && trim ($_POST ['name']) != '')
	$name = fileName ($_POST ['name']);
else
	$name = fileName ($_FILES ['file']['name']);

if (array_key_exists ('mimetype', $_POST) && trim ($_POST ['mimetype']) != '')
	$mime = trim ($_POST ['mimetype']);
else
{
	$mime = $_FILES ['file']['type'];
	
	$genericMimes = array ('application/octet-stream', 'binary');
	
	if (in_array ($mime, $genericMimes))
		$mime = $archive->getMimeByExtension (array_pop (explode ('.', $_FILES ['file']['name'])));
}

if (!$archive->isAcceptable ($mime))
	throw new ApiException (__ ('This type of file is not accepted by the system ([1])!', $mime), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

try
{
	$db->beginTransaction ();
	
	if (!$obj)
	{
		$id = Database::nextId ('_cloud', '_id');
		
		$sql = "INSERT INTO _cloud (_id, _code, _name, _mimetype, _size, _user, _author, _devise, _change, _ready, _update) VALUES (:id, :code, :name, :mime, :size, :user, :author, :devise, :change, B'1', NOW())";
		
		$sth = $db->prepare ($sql);
		
		$sth->bindParam (':id', $id, PDO::PARAM_INT);
		$sth->bindParam (':code', $code, PDO::PARAM_STR);
		$sth->bindParam (':name', $name, PDO::PARAM_STR, 256);
		$sth->bindParam (':mime', $mime, PDO::PARAM_STR, 256);
		$sth->bindParam (':size', $size, PDO::PARAM_INT);
		$sth->bindParam (':user', $user, PDO::PARAM_INT);
		$sth->bindParam (':author', $author, PDO::PARAM_INT);
		$sth->bindParam (':devise', $devise, PDO::PARAM_STR);
		$sth->bindParam (':change', $change, PDO::PARAM_STR);
	}
	else
	{
		$id = $obj->_id;
		
		$sql = "UPDATE _cloud SET _name = :name, _mimetype = :mime, _size = :size, _user = :user, _author = :author, _devise = :devise, _change = :change, _update = NOW(), _ready = B'1', _deleted = B'0'
				WHERE _id = :id";
		
		$sth = $db->prepare ($sql);
		
		$sth->bindParam (':id', $id, PDO::PARAM_INT);
		$sth->bindParam (':name', $name, PDO::PARAM_STR, 256);
		$sth->bindParam (':mime', $mime, PDO::PARAM_STR, 256);
		$sth->bindParam (':size', $size, PDO::PARAM_INT);
		$sth->bindParam (':user', $user, PDO::PARAM_INT);
		$sth->bindParam (':author', $author, PDO::PARAM_INT);
		$sth->bindParam (':devise', $devise, PDO::PARAM_STR);
		$sth->bindParam (':change', $change, PDO::PARAM_STR);
	}
	
	$sth->execute ();
	
	$file = realpath ($archive->getDataPath ()) . DIRECTORY_SEPARATOR .'cloud_'. str_pad ($id, 7, '0', STR_PAD_LEFT);
	
	if (!move_uploaded_file ($temp, $file))
		if (!rename ($temp, $file))
			throw new Exception (__ ('Unable copy file to directory [[1]]!', $temp .' > '. $file));
	
	$db->commit ();
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

if (function_exists ('curl_version'))
{
	$ch = curl_init ();

	curl_setopt ($ch, CURLOPT_URL, Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=encode&fileId='. $id);
	curl_setopt ($ch, CURLOPT_FRESH_CONNECT, TRUE);
	curl_setopt ($ch, CURLOPT_TIMEOUT_MS, 1);
	 
	curl_exec ($ch);
	
	curl_close ($ch);
}