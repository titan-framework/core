<?php

if (!$field->useEmbeddedImages () || empty ($field->getValue ()))
	return $field->getValue ();

$doc = new DOMDocument ();

$doc->loadHTML (mb_convert_encoding ($field->getValue (), 'HTML-ENTITIES', 'UTF-8'));

$tags = $doc->getElementsByTagName ('img');

foreach ($tags as $tag)
{
	$src = $tag->getAttribute ('src');

	preg_match ('/target=tScript\&type=File\&file=open\&id=([0-9]+)/i', $src, $result);

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

	try
	{
		$style = $tag->getAttribute ('style');

		preg_match ('/height:[\s]*([0-9]+)px;[\s]*width:[\s]*([0-9]+)px/i', $style, $result);

		if (sizeof ($result) != 3 || !(int) $result [1] || !(int) $result [2])
			throw new Exception ();

		$path = File::resize ($fileId, $type, $result [2], $result [1], TRUE, FALSE, FALSE, $field->useEmbeddedWebP (), $field->useEmbeddedJp2 ());
	}
	catch (Exception $e)
	{}

	$data = file_get_contents ($path);

	$base64 = 'data:'. mime_content_type ($path) .';base64,' . base64_encode ($data);

	$tag->setAttribute ('src', $base64);
}

return substr ($doc->saveHTML ($doc->getElementsByTagName ('body')->item (0)), 6, -7);
