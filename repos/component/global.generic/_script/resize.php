<?
try
{
	if (!isset ($_GET ['photoId']) || !$_GET['photoId'])
		throw new Exception (__ ('Error! Data losted.'));

	$photoId = $_GET['photoId'];

	$size = isset ($_GET['size']) ? $_GET['size'] : '500x0';

	$force = isset ($_GET['force']) && (int) $_GET['force'] ? TRUE : FALSE;

	$db = Database::singleton ();

	$sth = $db->prepare ("SELECT _name, _size, _mimetype FROM _media WHERE _id = ". $photoId);

	$sth->execute ();

	$obj = $sth->fetch (PDO::FETCH_OBJ);

	if (!$obj)
		throw new Exception (__ ('The photo [ [1] ] does not exist into DB!', $photoId));

	$cache = Instance::singleton ()->getCachePath ();

	$resized = $cache . 'gallery/resized_' . str_pad ($photoId, 7, '0', STR_PAD_LEFT) .'_'. $size .'_'. ($force ? '1' : '0');

	if (file_exists ($resized))
	{
		header ('Content-Type: '. $obj->_mimetype);
		header ('Content-Disposition: inline; filename=' . fileName ($obj->_name));

		$binary = fopen ($resized, 'rb');

		$buffer = fread ($binary, filesize ($resized));

		fclose ($binary);

		echo $buffer;

		exit ();
	}

	$rSize = explode ('x', $size);

	if (sizeof ($rSize) < 2)
		$rSize = array (500, 0);

	if (!is_numeric ($rSize [0]))
		$rSize [0] = 500;

	if (!is_numeric ($rSize [1]))
		$rSize [1] = 0;

	if (!$force && $rSize [0] < $rSize [1])
	{
		$aux = $rSize [1];
		$rSize [1] = $rSize [0];
		$rSize [0] = $aux;
	}

	if (!$rSize [0])
		$rSize [0] = 500;

	$archive = Archive::singleton ();

	$file = $archive->getDataPath () . 'photo_' . str_pad ($photoId, 7, '0', STR_PAD_LEFT);

	if (!class_exists ('Imagick', FALSE))
		resize ($file, $obj->_mimetype, $rSize [0], $rSize [1], $force);

	if (!file_exists ($cache . 'gallery/') && !@mkdir ($cache . 'gallery/', 0777))
		throw new Exception (__ ('Unable create directory [ [1] ]!', $cache ."gallery/"));

	$image = new Imagick ($file);

	$iSize ['x'] = $image->getImageWidth ();
	$iSize ['y'] = $image->getImageHeight ();

	if ($force && $rSize [0] && $rSize [1])
		$image->resizeImage ($rSize [0], $rSize [1], Imagick::FILTER_LANCZOS, 1);
	elseif ($iSize ['y'] > $iSize ['x'])
		$image->scaleImage ($rSize [1], $rSize [0]);
	else
		$image->scaleImage ($rSize [0], $rSize [1]);

	$image->writeImage ($resized);

	header ('Content-Type: '. $obj->_mimetype);
	header ('Content-Disposition: inline; filename=' . fileName ($obj->_name));

	echo $image;

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