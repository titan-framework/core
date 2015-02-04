<?
if (!isset ($_GET ['fileId']) || !$_GET['fileId'] || !is_numeric ($_GET['fileId']))
	die ();

$archive = Archive::singleton ();

if (isset ($_GET['width']) && is_numeric ($_GET['width']))
	$width = (int) $_GET['width'];
else
	$width = 0;

if (isset ($_GET['height']) && is_numeric ($_GET['height']))
	$height = (int) $_GET['height'];
else
	$height = 0;

$fileId = (int) $_GET ['fileId'];

try
{
	$db = Database::singleton ();
	
	$sth = $db->prepare ("SELECT _name, _size, _mimetype FROM _cloud WHERE _id = :id AND _ready = B'1' AND _deleted = B'0'");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new Exception ();
}
catch (PDOException $e)
{
	die ();
}
catch (Exception $e)
{
	die ();
}

if (isset ($_GET['assume']))
	$assume = (int) $_GET['assume'];
else
	$assume = $archive->getAssume ($obj->_mimetype);

if (!file_exists ($archive->getDataPath () . 'cloud_' . str_pad ($fileId, 7, '0', STR_PAD_LEFT)))
	die ();

switch ($assume)
{
	case Archive::IMAGE:
		
		if ($width && $height)
			CloudFile::resize ($fileId, $obj->_mimetype, $obj->_name, $width, $height, TRUE);
		
		if ($width || $height)
			CloudFile::resize ($fileId, $obj->_mimetype, $obj->_name, $width, $height);
		
		break;
}

$filePath = Instance::singleton ()->getCorePath () .'interface/file/' . $archive->getIcon ($obj->_mimetype) . '.gif';
toLog ($filePath);
$binary = fopen ($filePath, 'rb');

$buffer = fread ($binary, filesize ($filePath));

fclose ($binary);

header ('Content-Type: image/gif');

echo $buffer;
?>