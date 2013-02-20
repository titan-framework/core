<?
function replaceUnderScore ($input)
{
	foreach ($input as $key => $value)
	{
		$aux = str_replace ('_', '-', $key);

		if (array_key_exists ($aux, $input))
			continue;

		$input [$aux] = $value;
	}

	return $input;
}

function customizeTitan ($input, $file)
{
	if (!file_exists ($file))
		throw new Exception ('O arquivo ['. $file .'] não foi encontrado no caminho especificado.');

	$xml = new Xml ($file);

	$array = $xml->getArray ();

	if (!isset ($array ['titan-configuration'][0]))
		throw new Exception ('A tag &lt;titan-configuration&gt;&lt;/titan-configuration&gt; não existe no arquivo ['. $file .'].');

	$array = $array ['titan-configuration'][0];

	$categorys = array ('database', 'security', 'search', 'archive', 'business-layer', 'skin', 'mail', 'version-control', 'log', 'lucene');

	$original = array ();
	foreach ($categorys as $trash => $category)
	{
		if (array_key_exists ($category, $input))
			$input [$category] = replaceUnderScore ($input [$category]);

		if (isset ($array [$category][0]))
		{
			$original [$category] = $array [$category][0];
			unset ($array [$category]);
			unset ($original [$category][0]);
		}
    }

    if (array_key_exists ('main', $input))
		$input ['main'] = replaceUnderScore ($input ['main']);

	$original ['main'] = $array;

	foreach ($original as $category => $trash)
		if (array_key_exists ($category, $input))
			foreach ($original [$category] as $key => $value)
				if (array_key_exists ($key, $input [$category]))
					$original [$category][$key] = $input [$category][$key];

	$xml = new XmlMaker ();

	$xml->push ('titan-configuration', $original ['main']);

	foreach ($categorys as $trash => $category)
		$xml->emptyElement ($category, $original [$category]);

	$xml->pop ();

	return file_put_contents ($file, $xml->getXml());
}

function replace ($dir, $tags, $replace, $verbose = FALSE)
{
	$noReplace = array ('.', '..', 'Thumbs.db', '.svn', 'readme.txt');

	if($curdir = opendir($dir))
	{
		while($file = readdir($curdir))
		{
			if (!in_array ($file, $noReplace))
			{
				$file = $dir . DIRECTORY_SEPARATOR . $file;

				if (is_file($file))
				{
					$buffer = file_get_contents ($file);

					$buffer = str_replace ($tags, $replace, $buffer);

					file_put_contents ($file, $buffer);
				}
				elseif (is_dir ($file))
					replace ($file, $tags, $replace, $verbose);
			}
		}

		closedir($curdir);
	}
}

function changeTags (&$value, $key)
{
	$value = '['. $value .']';
}

function changeValue (&$value)
{
	if ($value == 1)
		return 'true';

	return 'false';
}
?>