<?
try
{
	if (!isset ($_GET ['photoId']) || !$_GET['photoId'])
		throw new Exception (__ ('Error! Data losted.'));

	$size = isset ($_GET['size']) ? $_GET['size'] : '120x90';

	$photoId = $_GET['photoId'];

	$db = Database::singleton ();

	$sth = $db->prepare ("SELECT _name, _size, _mimetype FROM _media WHERE _id = ". $photoId);

	$sth->execute ();

	$obj = $sth->fetch (PDO::FETCH_OBJ);

	if (!$obj)
		throw new Exception (__ ('The photo [ [1] ] does not exist into DB!', $photoId));

	$cache = Instance::singleton ()->getCachePath ();

	$thumb = $cache . 'gallery/thumb_' . str_pad ($photoId, 7, '0', STR_PAD_LEFT) .'_'. $size;

	if (file_exists ($thumb))
	{
		header ('Content-Type: image/png');
		header ('Content-Disposition: inline; filename=' . fileName ($obj->_name));

		$binary = fopen ($thumb, 'rb');

		$buffer = fread ($binary, filesize ($thumb));

		fclose ($binary);

		echo $buffer;

		exit ();
	}

	if (!file_exists ($cache . 'gallery/') && !@mkdir ($cache . 'gallery/', 0777))
		throw new Exception (__ ('Unable create directory [ [1] ]!', $cache."gallery/"));

	$array = explode ('x', $size);

	$archive = Archive::singleton ();

	$file = $archive->getDataPath () . 'photo_' . str_pad ($photoId, 7, '0', STR_PAD_LEFT);

	if (!class_exists ('Imagick', FALSE))
		resize ($file, $obj->_mimetype, $array [0], $array [1], TRUE);

	$img = new Imagick ($file);

	$img->setImageFormat ('png');

	$img->thumbnailImage ($array [0], $array [1]);

	$corner = floor ($array [0] / 24);

	$img->roundCorners ($corner, $corner);

	$shadow = $img->clone ();

	$shadow->setImageBackgroundColor (new ImagickPixel ('black'));

	$shadow->shadowImage (80, 3, 5, 5);

	$shadow->compositeImage ($img, Imagick::COMPOSITE_OVER, 0, 0);

	$shadow->writeImage ($thumb);

	echo $shadow;

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