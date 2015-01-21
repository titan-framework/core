<?php

if (Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [2]) || trim ($_uri [2]) == '')
	throw new ApiException (__ ('Invalid URI! The CODE of note is required!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$code = trim ($_uri [2]);

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException (__ ('Invalid user!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

if (!isset ($_POST ['title']) || trim ($_POST ['title']) == '' ||
	!isset ($_POST ['longitude']) || !is_numeric ($_POST ['longitude']) ||
	!isset ($_POST ['latitude']) || !is_numeric ($_POST ['latitude']) ||
	!isset ($_POST ['altitude']) || !is_numeric ($_POST ['altitude']) ||
	!isset ($_POST ['change']) || !is_numeric ($_POST ['change']) || !(int) $_POST ['change'])
	throw new ApiException (__ ('Required field is missing or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$db = Database::singleton ();

$sth = $db->prepare ("SELECT _id FROM _note WHERE _code = :code AND _user = :user AND
					  _change IS NULL AND _devise IS NULL AND _author IS NULL");

$sth->bindParam (':code', $code, PDO::PARAM_STR);
$sth->bindParam (':user', $user, PDO::PARAM_INT);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	throw new ApiException (__ ('This note does not exists or you cannot edit it!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$id = (int) $obj->_id;

$title = trim ($_POST ['title']);
$note = trim (@$_POST ['note']);
$longitude = (float) trim ($_POST ['longitude']);
$latitude = (float) trim ($_POST ['latitude']);
$altitude = (float) trim ($_POST ['altitude']);
$change = date ('Y-n-j H:i:s', (int) trim ($_POST ['change']));

if (array_key_exists ('author', $_POST) && is_numeric ($_POST ['author']) && (int) $_POST ['author'])
	$author = (int) trim ($_POST ['author']);
else
	$author = $user;

if (array_key_exists ('devise', $_POST) && is_numeric ($_POST ['devise']) && (int) $_POST ['devise'])
	$devise = date ('Y-n-j H:i:s', (int) trim ($_POST ['devise']));
else
	$devise = $change;

if (isset ($_POST ['media']) && is_array ($_POST ['media']))
	$medias = $_POST ['media'];
else
	$medias = array ();

$sql = "UPDATE _note SET
			_title = :title, 
			_note = :note, 
			_longitude = :longitude, 
			_latitude = :latitude,
			_altitude = :altitude, 
			_user = :user, 
			_change = :change,
			_update = now(),
			_author = :author,
			_devise = :devise
		WHERE _id = :id";

$sthUpdateNote = $db->prepare ($sql);

$sql = "INSERT INTO _note_media (_id, _code, _type, _user, _date, _note, _longitude, _latitude, _altitude, _file)
		VALUES (:id, :code, :type, :user, :date, :note, :longitude, :latitude, :altitude, :file)";

$sthInsertMedia = $db->prepare ($sql);

$sql = "INSERT INTO _cloud (_id, _code, _user) VALUES (:id, :code, :user)";

$sthInsertCloud = $db->prepare ($sql);

try
{
	$db->beginTransaction ();
	
	$sthUpdateNote->bindParam (':id', $id, PDO::PARAM_INT);
	$sthUpdateNote->bindParam (':title', $title, PDO::PARAM_STR);
	$sthUpdateNote->bindParam (':note', $note, PDO::PARAM_STR);
	$sthUpdateNote->bindParam (':longitude', $longitude);
	$sthUpdateNote->bindParam (':latitude', $latitude);
	$sthUpdateNote->bindParam (':altitude', $altitude);
	$sthUpdateNote->bindParam (':user', $user, PDO::PARAM_INT);
	$sthUpdateNote->bindParam (':change', $change);
	$sthUpdateNote->bindParam (':author', $author, PDO::PARAM_INT);
	$sthUpdateNote->bindParam (':devise', $devise);
	
	$sthUpdateNote->execute ();
	
	foreach ($medias as $key => $media)
	{
		if (!isset ($media ['code']) || trim ($media ['code']) == '' ||
			!isset ($media ['type']) || strlen (trim ($media ['type'])) != 5 ||
			!isset ($media ['file']) || trim ($media ['file']) == '' ||
			!isset ($media ['longitude']) || !is_numeric ($media ['longitude']) ||
			!isset ($media ['latitude']) || !is_numeric ($media ['latitude']) ||
			!isset ($media ['altitude']) || !is_numeric ($media ['altitude']) ||
			!isset ($media ['date']) || !is_numeric ($media ['date']) || !(int) $media ['date'])
			throw new ApiException (__ ('Required file parameter is missing or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);
		
		$cloudCode = trim ($media ['file']);
		
		$cloudId = Database::nextId ('_cloud', '_id');
		
		$sthInsertCloud->bindParam (':id', $cloudId, PDO::PARAM_INT);
		$sthInsertCloud->bindParam (':code', $cloudCode, PDO::PARAM_STR);
		$sthInsertCloud->bindParam (':user', $user, PDO::PARAM_INT);
		
		$sthInsertCloud->execute ();
		
		$mediaCode = trim ($media ['code']);
		$mediaType = trim ($media ['type']);
		$mediaLongitude = (float) trim ($media ['longitude']);
		$mediaLatitude = (float) trim ($media ['latitude']);
		$mediaAltitude = (float) trim ($media ['altitude']);
		$mediaDate = date ('Y-n-j H:i:s', (int) trim ($media ['date']));
		
		$mediaId = Database::nextId ('_note_media', '_id');
		
		$sthInsertMedia->bindParam (':id', $mediaId, PDO::PARAM_INT);
		$sthInsertMedia->bindParam (':code', $mediaCode, PDO::PARAM_STR);
		$sthInsertMedia->bindParam (':type', $mediaType, PDO::PARAM_STR);
		$sthInsertMedia->bindParam (':user', $user, PDO::PARAM_INT);
		$sthInsertMedia->bindParam (':date', $mediaDate);
		$sthInsertMedia->bindParam (':note', $id, PDO::PARAM_INT);
		$sthInsertMedia->bindParam (':longitude', $mediaLongitude);
		$sthInsertMedia->bindParam (':latitude', $mediaLatitude);
		$sthInsertMedia->bindParam (':altitude', $mediaAltitude);
		$sthInsertMedia->bindParam (':file', $cloudId, PDO::PARAM_INT);
		
		$sthInsertMedia->execute ();
	}
	
	$db->rollBack ();
	
	Log::singleton ()->add ('API-NOTE-CREATE', implode ('/', $_uri) ."\n\n". print_r ($_POST, TRUE), Log::INFO, FALSE);
}
catch (ApiException $e)
{
	$db->rollBack ();
	
	throw $e;
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