<?
function compress ($dir, $verbose = FALSE)
{
	$files = array ();
	
	$noCompress = array ('.', '..', 'Thumbs.db', '.svn', 'browscap', 'firefox.zip');
	
	if($curdir = opendir($dir)) 
	{
		while($file = readdir($curdir)) 
		{
			if (!in_array ($file, $noCompress))
			{
				$file = $dir . DIRECTORY_SEPARATOR . $file;
				
				if (is_file($file))
					$files [] = $file;
				elseif (is_dir ($file))
					$files = array_merge ($files, compress ($file, $verbose));
			}
		}
		
		closedir($curdir);
	}
	
	return $files;
}

if (!isset ($_GET['name']))
	throw new Exception ('Hove perda de variáveis!');

$name = trim (str_replace (array ('..', '/', '\\'), '', $_GET['name']));

if ($name == '')
	throw new Exception ('Atenção! Ato ilícito detectado. Acesso negado ;)');

$instance = Instance::singleton ();

$coreParth = $instance->getCorePath ();

require $corePath .'system/control.php';

require $corePath .'extra/pear.php';
require $corePath .'extra/zip.php';

$path = 'instance/'. $name;

$file = $instance->getCachePath () . $name .'_'. date ('YmdHmi') .'_'. randomHash (5) .'.zip';

$zip = new Archive_Zip ($file);

$files = compress ($path, TRUE);

if (!$zip->create ($files, array ('remove_path' => $path)))
	throw new Exception ('Erro ao tentar criar arquivo Zip: '. $zip->_error_string);

sleep (8);

$binary = fopen ($file, 'rb');

$buffer = fread ($binary, filesize ($file));

fclose ($binary);

@unlink ($file);

header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename='. $name .'_'. date ('YmdHmi') .'.zip');

echo $buffer;

exit ();
?>