<?php

if (!isset ($_GET ['fileId']) || !$_GET['fileId'] || !is_numeric ($_GET['fileId']))
	exit ();

ob_clean();

@apache_setenv ('no-gzip', 1);

@ini_set ('zlib.output_compression', 'Off');

require __DIR__ . DIRECTORY_SEPARATOR .'streaming.php';

$fileId = (int) $_GET ['fileId'];

try
{
	if (!$fileId)
		throw new Exception ('Invalid file!');
	
	$db = Database::singleton ();
	
	$sth = $db->prepare ("SELECT * FROM _cloud WHERE _id = :id AND _ready = B'1' AND _deleted = B'0'");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new Exception ('This file is not available!');
	
	if (!is_null (@$obj->_public) && !(int) $obj->_public)
	{
		// TODO: permission control
		// throw new Exception ('Permission denied!');
	}
	
	$archive = Archive::singleton ();
	
	if (!$archive->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type is not supported!');

	$file = $archive->getDataPath () . 'cloud_' . str_pad ($fileId, 7, '0', STR_PAD_LEFT);
	
	if (!file_exists ($file))
		throw new Exception ('This file is not available!');
	
	$supportedHtml5Types = array ('video/mp4', 'video/webm', 'video/ogg', 'audio/mpeg', 'audio/ogg', 'audio/wav');
	
	if (!in_array ($obj->_mimetype, $supportedHtml5Types))
	{
		switch ($obj->_mimetype)
		{
			case 'audio/3gpp':
			case 'audio/3gpp2':
				
				$cache = Instance::singleton ()->getCachePath ();
				
				$encoded = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($fileId, 7, '0', STR_PAD_LEFT) .'.ogg';
				
				if (file_exists ($encoded) && is_readable ($encoded) && filesize ($encoded))
				{
					$file = $encoded;
					
					break;
				}
				
				if (!file_exists ($cache . 'cloud-file') && !@mkdir ($cache . 'cloud-file', 0777))
					throw new Exception ('Unable create cache directory!');
				
				if (!file_exists ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
					throw new Exception ('Impossible to enhance security for folder ['. $cache . 'cloud-file].');
				
				if (!function_exists ('system'))
					throw new Exception ("Is needle enable OS call functions (verify if PHP is not in safe mode)!");
				
				$log = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'audio-encode.log';
				
				// MP3 Stereo Best Quality: avconv -y -i file/cloud_0000016 -acodec libmp3lame -ab 192k -ac 2 -ar 44100 cache/cloud-file/encoded_0000016.mp3
				// MP3 Mono Poor Quality: avconv -y -i file/cloud_0000016 -acodec libmp3lame -ab 64k -ac 1 -ar 22050 cache/cloud-file/encoded_0000016.mp3
				// OGG: avconv -y -i "file/cloud_0000016" -acodec libvorbis -ac 2 "cache/cloud-file/encoded_0000016.ogg"
				
				system ('avconv -y -i "'. $file .'" -acodec libvorbis -ac 2 "'. $encoded .'" 2> "'. $log .'"', $return);
			
				if ($return)
					throw new Exception ('Has a problem with audio/video conversion! Verify if [avconv] exists in system and supports OGG codec (libvorbis). Read more in LOG file ['. $log .'].');
				
				$file = $encoded;
				
				break;
		}
		
	}
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
	
	die ('Critical error! See the LOG file.');
}
catch (Exception $e)
{
	toLog ($e->getMessage ());
	
	die ($e->getMessage ());
}

try
{
	$sth = $db->prepare ("UPDATE _cloud SET _counter = _counter + 1 WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
}
catch (PDOException $e)
{}

$stream = new VideoStream ($file);

$stream->start ();