<?php

if (!$field->useEmbeddedImages () || empty ($field->getValue ()))
	return $field->getValue ();

$doc = new DOMDocument ();

$doc->loadHTML (mb_convert_encoding ($field->getValue (), 'HTML-ENTITIES', 'UTF-8'));

$tags = $doc->getElementsByTagName ('img');

foreach ($tags as $tag)
{
	$src = $tag->getAttribute ('src');

	preg_match ('/\&id=([0-9]+)/i', $src, $result);

	if (sizeof ($result) != 2 || !(int) $result [1])
		continue;

	$fileId = $result [1];

	$path = File::getFilePath ($fileId);

	if (!file_exists ($path))
	{
		$path = File::getLegacyFilePath ($fileId);

		if (!file_exists ($path))
			continue;
	}

	$type = Database::singleton ()->query ("SELECT _mimetype FROM _file WHERE _id = '". $fileId ."'")->fetchColumn ();

	$data = file_get_contents ($path);

	$base64 = 'data:image/' . $type . ';base64,' . base64_encode ($data);

	$tag->setAttribute ('src', $base64);
}

return substr ($doc->saveHTML ($doc->getElementsByTagName ('body')->item (0)), 6, -7);
