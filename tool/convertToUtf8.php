<pre>
<?
error_reporting (E_ALL);
set_time_limit (0);
ini_set ('memory_limit', '-1');

require Instance::singleton ()->getCorePath () .'extra/Encoding.php';

try
{
	$instancePath = getcwd ();
	
	echo "INFO      > Starting converting proccess... \n";
	
	$dbPath = $instancePath . DIRECTORY_SEPARATOR .'db.sql';
	
	echo "INFO      > Searching for DUMP of database on [". $dbPath ."]... \n";
	
	if (!file_exists ($dbPath) || !is_writable ($dbPath))
		echo "ERROR     > Impossible to find database dump file at [". $dbPath ."]! You need generate if you want convert instance database. \n";
	else
	{
		echo "INFO      > Converting database dump to UTF-8 [". $dbPath ."]... \n";
		
		$db = Instance::singleton ()->getDatabase ();
		
		$schema = isset ($db ['schema']) && trim ($db ['schema']) != '' ? trim ($db ['schema']) : 'public';
		
		$sql = Encoding::toUTF8 (file_get_contents ($dbPath));
		
		$sql = str_replace ("SET client_encoding = 'LATIN1';", "SET client_encoding = 'UTF8';", $sql). "\n\n";
		
		$sql .= "CREATE FUNCTION ". $schema .".to_ascii(bytea, name) RETURNS text STRICT AS 'to_ascii_encname' LANGUAGE internal; \n\n";
		
		$sql .= "CREATE FUNCTION ". $schema .".no_accents(text) RETURNS text  AS $$ SELECT translate($1,'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇ','aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcC'); $$ LANGUAGE sql IMMUTABLE STRICT; \n\n";
		
		if (!file_put_contents ($instancePath . DIRECTORY_SEPARATOR .'db-utf8.sql', $sql))
			throw new Exception ("Impossible to generate database dump converted to UTF-8!");
		
		echo "SUCCESS   > Database dump converted to UTF-8! [". $instancePath . DIRECTORY_SEPARATOR ."db-utf8.sql] \n";
	}
	
	echo "INFO      > Converting instance to UTF-8 [". $instancePath ."]... \n";
	
	convert ($instancePath);
	
	echo "SUCCESS   > All files converted to UTF-8! \n";
	
	echo "INFO      > The automatic way inside proccess is done! You need now search for specific functions like 'utf8_encode' ou 'utf8_decode' on your local components and webservices and, manually, fix code. \n";
}
catch (Exception $e)
{
	die ('CRITICAL  > '. $e->getMessage ());
}

function convert ($path)
{
	$remove = array ('.', '..', '.svn', 'xoad', 'extra');
	
	if (is_dir ($path))
	{
		chdir ($path);
		
		$exts = array ('xml', 'js', 'css', 'html', 'htm');
		
		foreach ($exts as $trash => $ext)
			foreach (glob ('*.'. $ext) as $file)
			{
				file_put_contents ($file, Encoding::toUTF8 (str_replace ('ISO-8859-1', 'UTF-8', file_get_contents ($file))));
				
				echo "CONVERTED > ". $path . DIRECTORY_SEPARATOR . $file ." [". mb_detect_encoding (file_get_contents ($file)) ."] \n";
			}
		
		$exts = array ('php', 'txt', 'ini');
		
		foreach ($exts as $trash => $ext)
			foreach (glob ('*.'. $ext) as $file)
			{
				file_put_contents ($file, Encoding::toUTF8 (file_get_contents ($file)));
				
				echo "CONVERTED > ". $path . DIRECTORY_SEPARATOR . $file ." [". mb_detect_encoding (file_get_contents ($file)) ."] \n";
			}
		
		$dh = opendir ($path);
		
		global $instancePath;
		
		$reserved = array (realpath ($instancePath . DIRECTORY_SEPARATOR . Instance::singleton ()->getCorePath ()),
						   realpath ($instancePath . DIRECTORY_SEPARATOR . Instance::singleton ()->getCachePath ()),
						   realpath ($instancePath . DIRECTORY_SEPARATOR . Archive::singleton ()->getDataPath ()));
		
		while (($file = readdir ($dh)) !== false)
		{
			if (in_array ($file, $remove))
				continue;
			
			$fullpath = realpath ($path . DIRECTORY_SEPARATOR . $file);
			
			if (!is_dir ($fullpath) || is_link ($fullpath) || in_array ($fullpath, $reserved))
				continue;
			
			convert ($fullpath);
		}
		
		closedir ($dh);
	}
}
?>
</pre>