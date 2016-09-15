<?php
function makeSeed ()
{
	return hexdec (substr (md5 (microtime()), -8)) & 0x7fffffff;
}

function zipFile ($itemId, $table, $fileTemp, $fileSize, $fileType)
{
	if (!file_exists ($fileTemp))
		throw new Exception (__ ('Missing ZIP file [ [1] ]!', $fileTemp));

	$zip = new Archive_Zip ($fileTemp);

	$cache = Instance::singleton ()->getCachePath () . 'unzip/';

	if (!file_exists ($cache) && !@mkdir ($cache, 0777))
		throw new Exception (__ ('Unable create directory [ [1] ]!', $cache));

	$count = 0;

	while (TRUE)
	{
		$hash = '';

		mt_srand (makeSeed ());

		while (strlen ($hash) < 5)
			$hash .= substr ('0123456789', mt_rand (0,9), 1);

		$aux = $cache . time () .'_'. User::singleton ()->getId () .'_'. $hash;

		if (file_exists ($aux))
			continue;

		if (@mkdir ($aux, 0777))
			break;

		if ($count++ >= 10)
			throw new Exception (__ ('Unable create directory [ [1] ]!', $aux));
	}

	$cache = $aux;

	$array = $zip->extract (array ('add_path' => $cache));

	$result = array ();

	$archive = Archive::singleton ();

	$db = Database::singleton ();

	foreach ($array as $trash => $file)
	{
		if (!is_file ($file ['filename']))
			continue;

		if (!($mimeType = Archive::mimeType ($file ['filename'])) && !($mimeType = $archive->getMimeByExtension (array_pop (explode ('.', $file ['filename'])))))
			continue;

		if (!$archive->isAcceptable ($mimeType, Archive::IMAGE))
			continue;

		$fileId = Database::nextId ('_media', '_id');

		$sth = $db->prepare ("INSERT INTO _media (_id, _name, _mimetype, _size, _user) VALUES ('". $fileId ."', '". $file ['filename'] ."', '". $mimeType ."', '". $file ['size'] ."', '". User::singleton ()->getId () ."')");

		$sth->execute ();

		$sth = $db->prepare ("INSERT INTO ". $table ." (_item, _media) VALUES ('". $itemId ."', '". $fileId ."')");

		$sth->execute ();

		if (!copy ($file ['filename'], $archive->getDataPath () . 'photo_'. str_pad ($fileId, 7, '0', STR_PAD_LEFT)))
			throw new Exception (__ ('The file can not be copied into folder [ [1] ]!', $archive->getDataPath ()));

		$result [] = $fileId;
	}

	set_error_handler ('logPhpError');

	removeDir ($cache);

	restore_error_handler ();

	return $result;
}
?>