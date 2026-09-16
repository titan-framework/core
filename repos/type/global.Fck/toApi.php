<?php

if (!$field->useEmbeddedImages () || empty ($field->getValue ()))
	return $field->getValue ();

/*
 * O HTML gravado pelo editor pode chegar malformado (ex.: "&" sem escape na URL da imagem,
 * como "...&type=File&file=open&id=N"). Sem suprimir os erros do libxml, DOMDocument::loadHTML()
 * emite E_WARNING ("htmlParseEntityRef: expecting ';'") e o handler da API (apiPhpError) responde
 * HTTP 500 para a listagem inteira por causa de um único registro. O libxml recupera o HTML mesmo
 * assim; basta não deixar o warning escapar.
 */
$useInternalErrors = libxml_use_internal_errors (TRUE);

$doc = new DOMDocument ();

$loaded = $doc->loadHTML (mb_convert_encoding ($field->getValue (), 'HTML-ENTITIES', 'UTF-8'));

libxml_clear_errors ();

libxml_use_internal_errors ($useInternalErrors);

if (!$loaded)
	return $field->getValue ();

$tags = $doc->getElementsByTagName ('img');

foreach ($tags as $tag)
{
	$src = $tag->getAttribute ('src');

	// Aceita "&" cru, "&amp;" e HTML duplamente codificado ("&amp;amp;").
	preg_match ('/target=tScript&(?:amp;)*type=File&(?:amp;)*file=open&(?:amp;)*id=([0-9]+)/i', $src, $result);

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

	// Arquivo ilegível (ou redimensionamento que devolveu caminho inválido) não pode virar warning: mantém a URL original.
	if (!is_string ($path) || !is_readable ($path))
		continue;

	$data = file_get_contents ($path);

	if ($data === FALSE)
		continue;

	$base64 = 'data:'. mime_content_type ($path) .';base64,' . base64_encode ($data);

	$tag->setAttribute ('src', $base64);
}

$body = $doc->getElementsByTagName ('body')->item (0);

if (!$body)
	return $field->getValue ();

return substr ($doc->saveHTML ($body), 6, -7);
