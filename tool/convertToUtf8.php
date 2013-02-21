<pre>
<?
error_reporting (E_ALL);
set_time_limit (0);
ini_set ('memory_limit', '-1');

require Instance::singleton ()->getCorePath () .'extra/Encoding.php';

try
{
	$instancePath = getcwd ();
	
	echo "INFO  > Starting converting proccess... \n";
	
	$dbPath = $instancePath . DIRECTORY_SEPARATOR .'db.sql';
	
	echo "INFO  > Searching for DUMP of database on [". $dbPath ."]... \n";
	
	if (!file_exists ($dbPath) || !is_writable ($dbPath))
		throw new Exception ('Impossible to find database dump file at ['. $dbPath .']! You need generate it at your instance root.');
	
	echo "INFO  > Converting database dump to UTF-8 [". $dbPath ."]... \n";
	
	$sql = Encoding::toUTF8 (file_get_contents ($dbPath));
	
	$sql = str_replace ("SET client_encoding = 'LATIN1';", "SET client_encoding = 'UTF8';", $sql);
	
	$sql = '\set ON_ERROR_STOP' . "\n\n" . $sql;
	
	file_put_contents ($instancePath . DIRECTORY_SEPARATOR .'db-utf8.sql', $sql);
	
	convert ($path);
	
	die ();
}
catch (Exception $e)
{
	die ('ERROR > '. $e->getMessage ());
}

function convert ($path)
{
	$remove = array ('.', '..', '.svn', 'xoad', 'extra');
	
	$exts = array ('php', 'xml', 'js', 'css', 'html', 'txt', 'htm');

	if (is_dir ($path))
	{
		/*
		chdir ($path);
		
		foreach ($exts as $trash => $ext)
			foreach (glob ('*.'. $ext) as $file)
			{
				file_put_contents ($file, Encoding::toUTF8 (file_get_contents ($file)));
				
				echo $path . DIRECTORY_SEPARATOR . $file ." [". mb_detect_encoding (file_get_contents ($file)) ."] \n";
			}
		*/
		$dh = opendir ($path);
		
		while (($file = readdir ($dh)) !== false)
		{
			if (in_array ($file, $remove))
				continue;
			
			$fullpath = realpath ($path . DIRECTORY_SEPARATOR . $file);
			
			if (!is_dir ($fullpath) || is_link ($fullpath) || $fullpath == realpath (Instance::singleton ()->getCorePath ()))
				continue;
			
			echo realpath ($fullpath) ."\n";
			// convert ($fullpath);
		}
		
		closedir ($dh);
	}
}

require 'Encoding.php';

$file = 'db.sql';

file_put_contents ('utf8-'. $file, '\set ON_ERROR_STOP'."\n\n".Encoding::toUTF8 (file_get_contents ($file)));
/*
$_TITAN = realpath ('pandora');

// chdir ($_TITAN);

echo "Starting for ". $_TITAN ."... \n";

convert ($_TITAN);
*/
echo "END";
?>
</pre>