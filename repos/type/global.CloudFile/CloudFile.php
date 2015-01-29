<?php

class CloudFile extends File
{	
	public function __construct ($table, $field)
	{
		if (!Database::tableExists ('_cloud'))
			throw new Exception ('The mandatory table [_cloud] do not exists! Its necessary to use type CloudFile.');
		
		parent::__construct ($table, $field);
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
}