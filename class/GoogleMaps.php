<?php

class GoogleMaps
{
	public static function geolocate ($ip = FALSE)
	{
		if ($ip === FALSE)
			$ip = $_SERVER ['REMOTE_ADDR'];
		
		if (ip2long ($ip) === FALSE)
			return array (0, 0);
		
		$path = Instance::singleton ()->getCachePath () .'geolocate';
		
		if (file_exists ($path . DIRECTORY_SEPARATOR . $ip))
			$json = file_get_contents ($path . DIRECTORY_SEPARATOR . $ip);
		else
		{
			$json = file_get_contents ('http://ipinfo.io/'. $ip .'/json');
			
			if (!file_exists ($path) && !@mkdir ($path, 0777))
				throw new Exception ('Impossible to create folder ['. $path .'].');
			
			if (!file_exists ($path . DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($path . DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
				throw new Exception ('Impossible to enhance security for folder ['. $path .'].');
			
			file_put_contents ($path . DIRECTORY_SEPARATOR . $ip, $json);
		}
		
		$obj = json_decode ($json);
		
		if (!is_object ($obj) || !isset ($obj->loc))
		{
			@unlink ($path . DIRECTORY_SEPARATOR . $ip);
			
			return array (0, 0);
		}
		
		$array = explode ($obj->loc, ',');
		
		if (sizeof ($array) != 2)
		{
			@unlink ($path . DIRECTORY_SEPARATOR . $ip);
			
			return array (0, 0);
		}
		
		return array ((float) $array [0], (float) $array [1]);
	}
}