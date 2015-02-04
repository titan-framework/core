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
			throw new Exception (__ ('The file has not been fully loaded and cannot be displayed until it is.'));
		
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
			throw new Exception (__ ('This type of file is not accepted by the system ([1]) !', $obj->mime));
		
		if (is_array ($filter) && (int) sizeof ($filter) && !in_array ($obj->mime, $filter))
			throw new Exception (__ ('This type of file is not accept at this field! Files accepts are: [1].', implode (', ', $filter)));
		
		ob_start ();
		
		switch ($archive->getAssume ($obj->mime))
		{
			case Archive::IMAGE:
				$alt = $obj->name ." (". CloudFile::formatFileSizeForHuman ($obj->size) ." &bull; ". $obj->mime .") \n". __ ('By [1] ([2]) on [3]', $obj->user, $obj->email, strftime ('%x %X', $obj->taken));
				?>
				<a href="titan.php?target=tScript&amp;type=CloudFile&amp;file=open&amp;fileId=<?= $id ?>" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&amp;type=CloudFile&amp;file=thumbnail&amp;fileId=<?= $id ?>&height=<?= $dimension ?>" alt="<?= $alt ?>" border="0" /></a>
				<?
				break;
			
			case Archive::VIDEO:
				?>
				<video width="320" height="240" controls="controls" preload="metadata">
					<source src="titan.php?target=tScript&type=CloudFile&file=play&fileId=<?= $id ?>" />
					<a href="titan.php?target=tScript&type=CloudFile&file=play&fileId=<?= $id ?>" target="_blank" title="<?= __ ('Play') ?>">
						<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
					</a>
				</video>
				<?
				break;
			
			case Archive::AUDIO:
				?>
				<audio controls="controls" preload="metadata">
					<source src="titan.php?target=tScript&type=CloudFile&file=play&fileId=<?= $id ?>" />
					<a href="titan.php?target=tScript&type=CloudFile&file=open&fileId=<?= $id ?>" target="_blank" title="<?= __ ('Play') ?>">
						<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
					</a>
				</audio>
				<?
				break;
			
			case Archive::DOWNLOAD:
			default:
				?>
				<div style="width: 343px; height: 106px;">
					<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
						<a href="titan.php?target=tScript&amp;type=CloudFile&amp;file=open&amp;fileId=<?= $id ?>" target="_blank"><img src="titan.php?target=tScript&amp;type=CloudFile&amp;file=thumbnail&amp;fileId=<?= $id ?>&width=100&height=100" border="0" /></a>
					</div>
					<div style="position: relative; width: 190px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF; text-align: left;">
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
		
		return ob_get_clean ();
	}
}