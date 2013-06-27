<?
try
{
	if (!Backup::singleton ()->isActive ())
		exit ();
	
	if (PHP_OS != 'Linux')
		throw new Exception ('This functionality works only in Linux servers (homologated on Debian and Ubuntu).');
	
	if (!User::singleton ()->isAdmin ())
		throw new Exception ('User '. User::singleton ()->getName () .' ['. User::singleton ()->getLogin () .'] dont have permission to backup data of system!');
	
	if (!Backup::singleton ()->getLock ())
		exit ();
	
	Backup::clear ();
	
	$_BENCHMARK = time ();
	
	$aux = explode (',', @$_GET ['artifacts']);
	
	$sizeFree = disk_free_space (Backup::singleton ()->getRealPath ());
	$sizeDB = Database::size ();
	$sizeFile = dirSize (realpath (Archive::singleton ()->getDataPath ()));
	$sizeCache = dirSize (realpath (Instance::singleton ()->getCachePath ()));
	
	$artifacts = array ('DB' => $sizeDB, 'FILE' => $sizeFile, 'CACHE' => $sizeCache);
	
	foreach ($artifacts as $key => $value)
		if (!in_array ($key, $aux))
			unset ($artifacts [$key]);
	
	if (!sizeof ($artifacts))
		throw new Exception ('Invalid parameters! There is no artifacts to make backup.');
	
	if (array_sum ($artifacts) > $sizeFree)
		throw new Exception ('There is no space on server disk to make backup.');
	
	$backup = Backup::singleton ();
	
	$user = User::singleton ();
	
	$instance = Instance::singleton ();
	
	set_time_limit ($backup->getTimeout ());
	
	$_NAME = $user->getName ();
	$_MAIL = $user->getEmail ();
	$_LGIN = $user->getLogin ();
	$_PASS = shortlyHash (randomHash ());
	
	$_UNTL = date ('d-m-Y', time () + $backup->getValidity ());
	
	$_PATH = $backup->getRealPath () . DIRECTORY_SEPARATOR . $backup->getFolder ();
	
	$_LINK = array ();
	
	ob_start ();
	
	try
	{
		echo "\n INFO > Starting backup... \n";
		
		echo "\n INFO > Enhancing security of backup folder... \n";
		
		if (!file_put_contents ($_PATH . DIRECTORY_SEPARATOR . '.htpasswd', $_LGIN .":". crypt ($_PASS, base64_encode ($_PASS))))
			throw new Exception ('Impossible to create .htpasswd file! Process aborted.');
		
		$htaccess = "AuthUserFile ". $_PATH . DIRECTORY_SEPARATOR .".htpasswd
AuthType Basic
AuthName \"Backup files of ". $instance->getName () ." at ". date ('d-m-Y H:i:s') ."\"
Require valid-user";
		
		if (!file_put_contents ($_PATH . DIRECTORY_SEPARATOR . '.htaccess', $htaccess))
			throw new Exception ('Impossible to create .htaccess file! Process aborted.');
		
		echo "\n INFO > Security enhanced with success! \n";
		
		if (array_key_exists ('DB', $artifacts))
		{
			echo "\n INFO > Starting DB backup... \n";
			
			$db = Database::singleton ();
			
			$file = $db->getName () .'.'. $db->getDbms () .'.'. date ('Ymd-His') .'.sql';
			
			if (in_array ($db->getHost (), array ('localhost', '127.0.0.1', '::1')))
				$command = '/usr/bin/pg_dump -U "'. $db->getUser () .'" -Fp -O "'. $db->getName () .'" > "'. $_PATH . DIRECTORY_SEPARATOR . $file .'"';
			else
				$command = '/usr/bin/pg_dump -h "'. $db->getHost () .'" -p '. $this->getPort () .' -U "'. $db->getUser () .'" -Fp -O "'. $db->getName () .'" > "'. $_PATH . DIRECTORY_SEPARATOR . $file .'"';
			
			echo "\n COMMAND > ". $command ."\n";
			
			system ($command, $return);
			
			if ($return)
				echo "\n ERROR> Impossible to make DB backup! \n";
			else
			{
				$command = '/bin/gzip "'. $_PATH . DIRECTORY_SEPARATOR . $file .'"';
				
				echo "\n COMMAND > ". $command ."\n";
				
				system ($command, $return);
				
				if ($return)
				{
					echo "\n ERROR > Impossible to compact DB backup!";
					
					$_LINK [] = $file;
				}
				else
				{
					echo "\n INFO > DB backup finished with success! \n";
				
					$_LINK [] = $file .'.gz';
				}
			}
		}
		
		if (array_key_exists ('FILE', $artifacts))
		{
			echo "\n INFO > Starting backup of uploaded files... \n";
			
			$file = 'file.'. date ('Ymd-His') .'.tar.gz';
			
			$command = '/bin/tar -czf "'. $_PATH . DIRECTORY_SEPARATOR . $file . '" -C "'. realpath (Archive::singleton ()->getDataPath ()) .'" "."';
			
			echo "\n COMMAND > ". $command ." \n";
			
			system ($command, $return);
			
			if ($return)
				echo "\n ERROR > Impossible to make backup of uploaded files! \n";
			else
			{
				echo "\n INFO > Backup of uploaded files finished with success! \n";
			
				$_LINK [] = $file;
			}
		}
		
		if (array_key_exists ('CACHE', $artifacts))
		{
			echo "\n INFO > Starting backup of cache folder... \n";
			
			$file = 'cache.'. date ('Ymd-His') .'.tar.gz';
			
			$command = '/bin/tar -czf "'. $_PATH . DIRECTORY_SEPARATOR . $file . '" -C "'. realpath (Instance::singleton ()->getCachePath ()) .'" "."';
			
			echo "\n COMMAND > ". $command ." \n";
			
			system ($command, $return);
			
			if ($return)
				echo "\n ERROR > Impossible to make backup of cache folder! \n";
			else
			{
				echo "\n INFO > Backup of cache folder finished with success! \n";
			
				$_LINK [] = $file;
			}
		}
		
		echo "\n INFO > All done after ". number_format (ceil ((time () - $_BENCHMARK) / 60), 0, ',', '.') ." minute(s)! To download backup, use the links bellow until ". $_UNTL .": \n";
		
		foreach ($_LINK as $trash => $link)
			echo "\n LINK > ". $instance->getUrl () . $backup->getPath () . $backup->getFolder () .'/'. $link ." (". number_format (filesize ($_PATH . DIRECTORY_SEPARATOR . $link) / (1024 * 1024), 0, ',', '.')  ." MB) \n";
		
		echo "\n INFO > You need access with login '". $_LGIN ."' and password '". $_PASS ."' (without quotes). \n";
		
		echo "\n Enjoy! \n";
		
		$output = ob_get_clean ();
		
		if (!@mail ($_MAIL .','. $instance->getEmail (), '=?utf-8?B?'. base64_encode ('['. $instance->getName () .'] Backup on demand at '. date ('d-m-Y H:i:s')) .'?=', $output, "Content-Type: text/plain; charset=utf-8"))
			throw new Exception ("Error to send mail [". $_MAIL ."]. Backup folder will be deleted. Output: \n\n". $output);
	}
	catch (Exception $e)
	{
		ob_flush ();
		
		removeDir ($_PATH);
		
		throw new Exception ($e->getMessage ());
	}
}
catch (Exception $e)
{
	toLog ($e->getMessage ());
}

Backup::singleton ()->releaseLock ();
?>