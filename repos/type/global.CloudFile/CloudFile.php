<?php

class CloudFile extends File
{	
	public function __construct ($table, $field)
	{
		if (!Database::tableExists ('_cloud'))
			throw new Exception ('The mandatory table [_cloud] do not exists! Its necessary to use type CloudFile.');
		
		parent::__construct ($table, $field);
	}
	
	public static function getFilePath ($id)
	{
		return Archive::singleton ()->getDataPath () . 'cloud_' . str_pad ($id, 7, '0', STR_PAD_LEFT);
	}
	
	public static function formatFileSizeForHuman ($bytes, $decimals = 0)
	{
		$size = array ('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		
		$factor = floor ((strlen ($bytes) - 1) / 3);
		
		return sprintf ("%.{$decimals}f", $bytes / pow (1024, $factor)) .' '. @$size [$factor];
	}
	
	public static function resize ($id, $type, $name, $width = 0, $height = 0, $force = FALSE, $bw = FALSE)
	{
		$cache = Instance::singleton ()->getCachePath ();
		
		$resized = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'resized_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'_'. $width .'x'. $height .'_'. ($force ? '1' : '0') .'_'. ($bw ? '1' : '0');
		
		if (file_exists ($resized) && is_readable ($resized) && filesize ($resized))
		{
			header ('Content-Type: '. $type);
			header ('Content-Disposition: inline; filename=' . fileName ($name));
			
			$binary = fopen ($resized, 'rb');
			
			$buffer = fread ($binary, filesize ($resized));
			
			fclose ($binary);
			
			echo $buffer;
			
			exit ();
		}
		
		$file = Archive::singleton ()->getDataPath () . 'cloud_' . str_pad ($id, 7, '0', STR_PAD_LEFT);
		
		$buffer = FALSE;
		
		switch ($type)
		{
			case 'image/jpeg':
			case 'image/pjpeg':
				$buffer = imagecreatefromjpeg ($file);
				break;
	
			case 'image/gif':
				$buffer = imagecreatefromgif ($file);
				break;
	
			case 'image/png':
				$buffer = imagecreatefrompng ($file);
				imagealphablending ($buffer, TRUE);
				imagesavealpha ($buffer, TRUE);
				break;
		}
	
		if (!$buffer)
			throw new Exception ('File mimetype is invalid or the image does not exists!');
		
		if ($bw)
			@imagefilter ($buffer, IMG_FILTER_GRAYSCALE);
		
		$vetor = getimagesize ($file);
	
		$atualWidth  = $vetor [0];
		$atualHeight = $vetor [1];
	
		if(!$force)
		{
			if (!$width || !$height)
			{
				if (!$width && !$height)
				{
					$width = $atualWidth;
					$height = $atualHeight;
				}
				elseif ($width && !$height)
					$height = ($atualHeight * $width) / $atualWidth;
				else
					$width = ($atualWidth * $height) / $atualHeight;
			}
	
			if ($atualWidth < $atualHeight && $width > $height)
			{
				$aux = $width;
				$width = $height;
				$height = $aux;
			}
	
			if ((int) $atualWidth < (int) $width)
			{
				$width = $atualWidth;
	
				$height = ($atualHeight * $width) / $atualWidth;
			}
		}
	
		if ($type != 'image/gif')
		{
			$thumb = imagecreatetruecolor ($width, $height);
			$color = imagecolorallocatealpha ($thumb, 255, 255, 255, 75);
			imagefill ($thumb, 0, 0, $color);
		}
		else
			$thumb = imagecreate ($width, $height);
	
		$ok = imagecopyresized ($thumb, $buffer, 0, 0, 0, 0, $width, $height, $atualWidth, $atualHeight);
	
		if (!$ok)
			throw new Exception ('Impossible to resize image!');
		
		if (!file_exists ($cache . 'cloud-file') && !@mkdir ($cache . 'cloud-file', 0777))
			throw new Exception ('Unable create cache directory!');
		
		if (!file_exists ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
			throw new Exception ('Impossible to enhance security for folder ['. $cache . 'cloud-file].');
		
		header ('Content-Type: '. $type);
	
		switch ($type)
		{
			case 'image/jpeg':
			case 'image/pjpeg':
				imagejpeg ($thumb, $resized, 100);
				imagejpeg ($thumb, NULL, 100);
				break;
	
			case 'image/gif':
				imagegif ($thumb, $resized);
				imagegif ($thumb);
				break;
	
			case 'image/png':
				imagepng ($thumb, $resized);
				imagepng ($thumb);
				break;
		}
	
		imagedestroy ($thumb);
	
		exit ();
	}
	
	public static function synopsis ($id, $filter = array (), $dimension = 200)
	{
		$path = self::getFilePath ($id);
		
		if (!file_exists ($path))
			throw new Exception (__ ('The file has not been fully sended to server and cannot be displayed until it is.'));
		
		try
		{
			$db = Database::singleton ();
			
			$sth = $db->prepare ("SELECT c._name AS name, c._size AS size, c._mimetype AS mime, u._name AS user, u._email AS email,
								  EXTRACT (EPOCH FROM _devise) AS taken
								  FROM _cloud c 
								  LEFT JOIN _user u ON u._id = c._user
								  WHERE c._id = :id AND c._ready = B'1' AND c._deleted = B'0'");
			
			$sth->bindParam (':id', $id, PDO::PARAM_INT);
			
			$sth->execute ();
			
			$obj = $sth->fetch (PDO::FETCH_OBJ);
		}
		catch (PDOException $e)
		{
			toLog ('['. $e->getLine () .'] '. $e->getMessage ());
			
			throw new Exception (__ ('There was a severe error when trying to load file! Please, contact your administrator.'));
		}
		
		if (!$obj)
			throw new Exception (__ ('There is no associated file!'));
		
		$archive = Archive::singleton ();
		
		if (!$archive->isAcceptable ($obj->mime))
			throw new Exception (__ ('This type of file is not accepted by the system ([1])!', $obj->mime));
		
		if (is_array ($filter) && (int) sizeof ($filter) && !in_array ($obj->mime, $filter))
		{
			$types = array ();
			
			foreach ($filter as $trash => $mime)
			{
				$aux = trim ($archive->getExtensionByMime ($mime));
				
				if (empty ($aux))
					continue;
				
				$types [] = strtoupper ($aux);
			}
			
			throw new Exception (__ ('This type of file ([1]) is not accept at this field! Files accepts are: [2].', $obj->mime, implode (', ', $types)));
		}
		
		ob_start ();
		
		switch ($archive->getAssume ($obj->mime))
		{
			case Archive::IMAGE:
				$alt = $obj->name ." (". CloudFile::formatFileSizeForHuman ($obj->size) ." &bull; ". $obj->mime .") \n". __ ('By [1] ([2]) on [3].', $obj->user, $obj->email, strftime ('%x %X', $obj->taken));
				?>
				<a href="titan.php?target=tScript&amp;type=CloudFile&amp;file=open&amp;fileId=<?= $id ?>" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&amp;type=CloudFile&amp;file=thumbnail&amp;fileId=<?= $id ?>&height=<?= $dimension ?>" alt="<?= $alt ?>" border="0" /></a>
				<?
				break;
			
			case Archive::VIDEO:
				
				if (self::isReadyToPlay ($id, $obj->mime))
				{
					?>
					<video width="320" height="240" controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=CloudFile&file=play&fileId=<?= $id ?>" />
						<a href="titan.php?target=tScript&type=CloudFile&file=play&fileId=<?= $id ?>" target="_blank" title="<?= __ ('Play') ?>">
							<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
						</a>
					</video>
					<?
				}
				else
				{
					$alt = $obj->name ." (". CloudFile::formatFileSizeForHuman ($obj->size) ." &bull; ". $obj->mime .") \n". __ ('By [1] ([2]) on [3].', $obj->user, $obj->email, strftime ('%x %X', $obj->taken));
					?>
					<div style="width: 343px; height: 106px;">
						<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
							<a href="titan.php?target=tScript&amp;type=CloudFile&amp;file=open&amp;fileId=<?= $id ?>" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&amp;type=CloudFile&amp;file=thumbnail&amp;fileId=<?= $id ?>&width=100&height=100" border="0" alt="<?= $alt ?>" /></a>
						</div>
						<div style="position: relative; width: 220px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF; text-align: justify;">
							<b style="color: #900;"><?= __ ('This video is not supported by native player of your browser or still is being encoded to be displayed! Until then, you can download it directly to your computer to watch in player of your choice.') ?></b>
						</div>
					</div>
					<?
				}
				break;
			
			case Archive::AUDIO:
				
				if (self::isReadyToPlay ($id, $obj->mime))
				{
					?>
					<audio controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=CloudFile&file=play&fileId=<?= $id ?>" />
						<a href="titan.php?target=tScript&type=CloudFile&file=open&fileId=<?= $id ?>" target="_blank" title="<?= __ ('Play') ?>">
							<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
						</a>
					</audio>
					<?
				}
				else
				{
					$alt = $obj->name ." (". CloudFile::formatFileSizeForHuman ($obj->size) ." &bull; ". $obj->mime .") \n". __ ('By [1] ([2]) on [3].', $obj->user, $obj->email, strftime ('%x %X', $obj->taken));
					?>
					<div style="width: 343px; height: 106px;">
						<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
							<a href="titan.php?target=tScript&amp;type=CloudFile&amp;file=open&amp;fileId=<?= $id ?>" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&amp;type=CloudFile&amp;file=thumbnail&amp;fileId=<?= $id ?>&width=100&height=100" border="0" alt="<?= $alt ?>" /></a>
						</div>
						<div style="position: relative; width: 220px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF; text-align: justify;">
							<b style="color: #900;"><?= __ ('This audio is not supported by native player of your browser or still is being encoded to be displayed! Until then, you can download it directly to your computer to listen in player of your choice.') ?></b>
						</div>
					</div>
					<?
				}
				break;
			
			case Archive::DOWNLOAD:
			case Archive::OPEN:
			default:
				?>
				<div style="width: 343px; height: 106px;">
					<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
						<a href="titan.php?target=tScript&amp;type=CloudFile&amp;file=open&amp;fileId=<?= $id ?>" target="_blank"><img src="titan.php?target=tScript&amp;type=CloudFile&amp;file=thumbnail&amp;fileId=<?= $id ?>&width=100&height=100" border="0" /></a>
					</div>
					<div style="position: relative; width: 220px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF; text-align: left;">
						<b><?= $obj->name ?></b> <br />
						<?= self::formatFileSizeForHuman ($obj->size) ?> <br />
						<?= $obj->mime ?> <br /><br />
						<?= $obj->user ?> <br />
						<?= $obj->email ?> <br />
						<?= strftime ('%x %X', $obj->taken) ?>
					</div>
				</div>
				<?
		}
		
		return str_replace ("\t", '', ob_get_clean ());
	}
	
	public static function getPlayableFile ($id, $mimetype)
	{
		$file = self::getFilePath ($id);
		
		if (!file_exists ($file) || !is_readable ($file) || !(int) filesize ($file))
			throw new Exception ('This file is not available!');
		
		$supportedHtml5Types = array ('video/mp4', 'video/webm', 'video/ogg', 'audio/mpeg', 'audio/ogg', 'audio/wav');
		
		if (in_array ($mimetype, $supportedHtml5Types))
			return $file;
		
		switch ($mimetype)
		{
			case 'audio/3gpp':
			case 'audio/3gpp2':
				
				$cache = Instance::singleton ()->getCachePath ();
				
				$encoded = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.ogg';
				
				if (file_exists ($encoded) && is_readable ($encoded) && filesize ($encoded))
					return $encoded;
				
				if (!file_exists ($cache . 'cloud-file') && !@mkdir ($cache . 'cloud-file', 0777))
					throw new Exception ('Unable create cache directory!');
				
				if (!file_exists ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
					throw new Exception ('Impossible to enhance security for folder ['. $cache . 'cloud-file].');
				
				if (!function_exists ('system'))
					throw new Exception ("Is needle enable OS call functions (verify if PHP is not in safe mode)!");
				
				$control = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.encoding';
				
				if (!file_put_contents ($control, strftime ('%c'), LOCK_EX))
					throw new Exception ('Impossible to create control file!');
				
				$log = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.3gp-ogg.log';
				
				// MP3 Stereo Best Quality: avconv -y -i file/cloud_0000016 -acodec libmp3lame -ab 192k -ac 2 -ar 44100 cache/cloud-file/encoded_0000016.mp3
				// MP3 Mono Poor Quality: avconv -y -i file/cloud_0000016 -acodec libmp3lame -ab 64k -ac 1 -ar 22050 cache/cloud-file/encoded_0000016.mp3
				// OGG: avconv -y -i "file/cloud_0000016" -acodec libvorbis -ac 2 "cache/cloud-file/encoded_0000016.ogg"
				
				system ('avconv -y -i "'. $file .'" -acodec libvorbis -ac 2 "'. $encoded .'" 2> "'. $log .'"', $return);
				
				unlink ($control);
				
				if ($return)
				{
					@unlink ($encoded);
					
					throw new Exception ('Has a problem with audio conversion! Verify if [avconv] exists in system and supports OGG codec (libvorbis). Read more in LOG file ['. $log .'].');
				}
				
				return $encoded;
			
			case 'video/quicktime':
				
				$cache = Instance::singleton ()->getCachePath ();
				
				$encoded = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.webm';
				
				if (file_exists ($encoded) && is_readable ($encoded) && filesize ($encoded))
					return $encoded;
				
				if (!file_exists ($cache . 'cloud-file') && !@mkdir ($cache . 'cloud-file', 0777))
					throw new Exception ('Unable create cache directory!');
				
				if (!file_exists ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
					throw new Exception ('Impossible to enhance security for folder ['. $cache . 'cloud-file].');
				
				if (!function_exists ('system'))
					throw new Exception ("Is needle enable OS call functions (verify if PHP is not in safe mode)!");
				
				$control = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.encoding';
				
				if (!file_put_contents ($control, strftime ('%c'), LOCK_EX))
					throw new Exception ('Impossible to create control file!');
				
				$log = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.mov-webm.log';
				
				system ('avconv -y -i "'. $file .'" "'. $encoded .'" 2> "'. $log .'"', $return);
			
				unlink ($control);
			
				if ($return)
				{
					@unlink ($encoded);
					
					throw new Exception ('Has a problem with video conversion! Verify if [avconv] exists in system and supports MP4 codec. Read more in LOG file ['. $log .'].');
				}
				
				return $encoded;
			
			case 'video/x-ms-wmv':
				
				$cache = Instance::singleton ()->getCachePath ();
				
				$encoded = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.webm';
				
				if (file_exists ($encoded) && is_readable ($encoded) && filesize ($encoded))
					return $encoded;
				
				if (!file_exists ($cache . 'cloud-file') && !@mkdir ($cache . 'cloud-file', 0777))
					throw new Exception ('Unable create cache directory!');
				
				if (!file_exists ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
					throw new Exception ('Impossible to enhance security for folder ['. $cache . 'cloud-file].');
				
				if (!function_exists ('system'))
					throw new Exception ("Is needle enable OS call functions (verify if PHP is not in safe mode)!");
				
				$control = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.encoding';
				
				if (!file_put_contents ($control, strftime ('%c'), LOCK_EX))
					throw new Exception ('Impossible to create control file!');
				
				$log = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.wmv-webm.log';
				
				system ('avconv -y -i "'. $file .'" "'. $encoded .'" 2> "'. $log .'"', $return);
				
				unlink ($control);
				
				if ($return)
				{
					@unlink ($encoded);
					
					throw new Exception ('Has a problem with video conversion! Verify if [avconv] exists in system and supports WebM codec. Read more in LOG file ['. $log .'].');
				}
				
				return $encoded;
			
			case 'audio/x-ms-wma':
				
				$cache = Instance::singleton ()->getCachePath ();
				
				$encoded = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.ogg';
				
				if (file_exists ($encoded) && is_readable ($encoded) && filesize ($encoded))
					return $encoded;
				
				if (!file_exists ($cache . 'cloud-file') && !@mkdir ($cache . 'cloud-file', 0777))
					throw new Exception ('Unable create cache directory!');
				
				if (!file_exists ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . 'cloud-file'. DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
					throw new Exception ('Impossible to enhance security for folder ['. $cache . 'cloud-file].');
				
				if (!function_exists ('system'))
					throw new Exception ("Is needle enable OS call functions (verify if PHP is not in safe mode)!");
				
				$control = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.encoding';
				
				if (!file_put_contents ($control, strftime ('%c'), LOCK_EX))
					throw new Exception ('Impossible to create control file!');
				
				$log = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.wma-ogg.log';
				
				system ('avconv -y -i "'. $file .'" "'. $encoded .'" 2> "'. $log .'"', $return);
				
				unlink ($control);
				
				if ($return)
				{
					@unlink ($encoded);
					
					throw new Exception ('Has a problem with video conversion! Verify if [avconv] exists in system and supports OGG codec (libvorbis). Read more in LOG file ['. $log .'].');
				}
				
				return $encoded;
		}
		
		return $file;
	}
	
	public static function isReadyToPlay ($id, $mimetype)
	{
		$convertible = array (
			'audio/3gpp' => 'ogg',
			'audio/3gpp2' => 'ogg',
			'video/quicktime' => 'webm',
			'video/x-ms-wmv' => 'webm',
			'audio/x-ms-wma' => 'ogg'
		);
		
		if (!in_array (Archive::singleton ()->getAssume ($mimetype), array (Archive::VIDEO, Archive::AUDIO)))
			return FALSE;
		
		if (!array_key_exists ($mimetype, $convertible))
			return TRUE;
		
		$cache = Instance::singleton ()->getCachePath ();
		
		$encoded = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.'. $convertible [$mimetype];
		
		$control = $cache . 'cloud-file'. DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 7, '0', STR_PAD_LEFT) .'.encoding';
		
		if (!file_exists ($encoded) || (!(int) filesize ($encoded) && (!file_exists ($control) || filemtime ($control) < strtotime ('-1 day'))))
		{
			self::assyncEncodeFile ($id);
			
			return FALSE;
		}
		
		if (file_exists ($control))
			return FALSE;
		
		return TRUE;
	}
	
	public static function assyncEncodeFile ($id)
	{
		if (!function_exists ('curl_version'))
			throw new Exception ('The PHP library cURL is not enable!');

		$ch = curl_init ();
	
		curl_setopt ($ch, CURLOPT_URL, Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=encode&fileId='. $id);
		curl_setopt ($ch, CURLOPT_FRESH_CONNECT, TRUE);
		curl_setopt ($ch, CURLOPT_TIMEOUT_MS, 1);
		 
		curl_exec ($ch);
		
		curl_close ($ch);
	}
}