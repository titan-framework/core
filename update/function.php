<?php
/**
 * Functions for script of auto update web applications.
 *
 * Copyright 2013 - PLEASE Lab / Embrapa Gado de Corte
 *
 * @author Camilo Carromeu <camilo.carromeu@embrapa.br>
 * @author Jairo Ricardes Rodrigues Filho <jairocgr@gmail.com>
 * @author Bruno Righes <brunorighes@gmail.com>
 * @version 1.0
 * 
 * File with collection of functions necessary to auto update script.
 */

function setPermission ($path, $dMode, $fMode, $owner, $group)
{
	$remove = array ('.', '..', '.svn');
	
	if (is_dir ($path))
	{
		if (!chown ($path, $owner) || !chgrp ($path, $group) || !chmod ($path, $dMode))
		{
			echo "ERROR > Impossible to set permission [". $dMode ."], owner [". $owner ."] or group [". $group ."] to folder [". $path ."]! \n";
			
			return;
		}
		
		$dh = opendir ($path);
		
		while (($file = readdir ($dh)) !== false)
		{
			if (in_array ($file, $remove))
				continue;
			
			$fullpath = $path . DIRECTORY_SEPARATOR . $file;
			
			setPermission ($fullpath, $dMode, $fMode, $owner, $group);
		}
		
		closedir ($dh);
	}
	else
	{
		if (!file_exists ($path))
		{
			echo "ERROR > File do not exists [". $path ."]! \n";
			
			return;
		}
		
		if (is_link ($path))
		{
			echo "ERROR > Impossible make changes at symbolic links [". $path ."]! \n";
			
			return;
		}
		
		if (!chown ($path, $owner) || !chgrp ($path, $group) || !chmod ($path, $fMode))
		{
			echo "ERROR > Impossible to set permission [". $fMode ."], owner [". $owner ."] or group [". $group ."] to file [". $path ."]! \n";
			
			return;
		}
	}
}

function backupDatabase ($name, $host, $port, $user, $password, $folder, $validity)
{
	static $done = array ();
	
	if (in_array ($name .'@'. $host, $done))
		return;
	
	$folder = realpath ($folder);
	
	if ($folder == '')
		throw new Exception ("Empty folder to make DB backup! Verify in 'titan.xml' if backup folder at tag 'backup' is not out of 'open_basedir' restriction.");
	
	$invalid = array (DIRECTORY_SEPARATOR, '/dev/null');
	
	if (in_array ($folder, $invalid))
		throw new Exception ("Ivalid folder to make DB backup [". $folder ."]!");
	
	$folder .= DIRECTORY_SEPARATOR .'auto-update';
	
	echo "INFO > Starting DB backup [". $name ."] at folder [". $folder ."]... \n";
	
	if (is_dir ($folder))
	{
		echo "INFO > Removing file older than ". $validity ." seconds... \n";
		
		$files = glob ($folder . DIRECTORY_SEPARATOR . $name .'.db.*');
		
		if (is_array ($files))
			foreach ($files as $trash => $file)
				if (!is_dir ($file) && !is_link ($file) && filemtime ($file) < strtotime ('-'. $validity .' seconds'))
				{
					unlink ($file);
					
					echo "INFO > Backup file deleted [". $file ."]! \n";
				}
	}
	else
	{
		echo "INFO > Creating database backup folder [". $folder ."]... \n";
		
		system ('mkdir -p '. $folder, $return);
		
		if ($return)
			throw new Exception ("CRITICAL > Impossible to create DB backup folder! Update proccess aborted.");
		
		system ('echo "deny from all" > '. $folder . DIRECTORY_SEPARATOR . '.htaccess', $return);
	}
	
	$dumpName = $name .'.db.'. date ('YmdHis') .'.sql';
	
	echo "INFO > Generating DUMP file [". $folder ."/". $dumpName ."]... \n";
	
	system (SU .' - postgres -c "/usr/bin/pg_dump -Fp -O '. $name .' > /tmp/'. $dumpName .'"', $return);
	
	if ($return)
	{
		echo "INFO > Removing incorrect DB dump... \n";
		
		system (SU .' - postgres -c "'. RM .' -q /tmp/'. $dumpName .'"', $return);
		
		throw new Exception ("Impossible to generate DB dump at [/tmp/". $dumpName ."]! Update proccess aborted.");
	}
	
	system (MV .' "/tmp/'. $dumpName .'" "'. $folder . DIRECTORY_SEPARATOR . $dumpName .'"', $return);
	
	if ($return)
	{
		echo "INFO > Removing incorrect DB dump... \n";
		
		system (SU .' - postgres -c "rm -q /tmp/'. $dumpName .'"', $return);
		
		throw new Exception ("Impossible to move DB dump at [/tmp/". $dumpName ."] to [". $folder . DIRECTORY_SEPARATOR . $dumpName ."]! Update proccess aborted.");
	}
	
	system (GZIP .' -f '. $folder . DIRECTORY_SEPARATOR . $name .'.db.*.sql', $return);

	if ($return)
		echo "ERROR > Impossible to compact contents of DB backup folder [". $folder ."]! \n";

	// system ('/bin/df -h');
	
	echo "INFO > Backup finished! Applying update changes... \n";
	
	$done [] = $name .'@'. $host;
}

function tableExists ($db, $name)
{
	$array = explode ('.', $name);
	
	if (sizeof ($array) == 2)
	{
		$schema = $array [0];
		$table = $array [1];
	}
	else
	{
		$schema = 'public';
		$table = $array [0];
	}
	
	$sth = $db->prepare ("SELECT tablename FROM pg_tables WHERE schemaname = :schema AND tablename = :table");
	
	$sth->bindParam (':schema', $schema, PDO::PARAM_STR);
	$sth->bindParam (':table', $table, PDO::PARAM_STR);
	
	$sth->execute ();
	
	if ((int) $sth->rowCount ())
		return TRUE;
	
	return FALSE;
}

function handleError ($errno, $errstr, $errfile, $errline, $errcontext)
{
	if (error_reporting () === 0)
		return FALSE;
	
	throw new Exception ($errstr .' ['. $errno .' | '. $errfile .' | '. $errline .']');
}

function printChangelog ($conf, $path, $initial, $actual, $titanLog)
{
	if ($conf == 'NONE')
		return;
	
	if ($actual > $initial)
	{
		$log = svn_log ($path, $initial + 1, $actual);
		
		$changelog = array ();
		
		foreach ($log as $trash => $rev)
		{
			if (trim ($rev ['msg']) == '')
				continue;
		
			$output  = "Revision #". $rev ['rev'] ." of ". date ('d-m-Y H:i:s (P \G\M\T)', strtotime ($rev ['date'])) ." by ". $rev ['author'] ." \n";
			$output .= $rev ['msg'] ." \n";
			
			if ($conf == 'FULL')
				foreach ($rev ['paths'] as $trash => $file)
					$output .= $file ['action'] ." ". $file ['path'] ." \n";
			
			$output .= "\n";
			
			$changelog [] = $output;
		}
		
		if (sizeof ($changelog))
		{
			echo "INSTANCE CHANGELOG \n";
			echo "======== ========= \n\n";
		
			echo implode ("", $changelog);
		}
	}
	
	$changelog = array ();
	
	foreach ($titanLog as $trash => $rev)
	{
		if (trim ($rev ['msg']) == '')
			continue;
	
		$output  = "Revision #". $rev ['rev'] ." of ". date ('d-m-Y H:i:s (P \G\M\T)', strtotime ($rev ['date'])) ." by ". $rev ['author'] ." \n";
		$output .= $rev ['msg'] ." \n";
		
		if ($conf == 'FULL')
			foreach ($rev ['paths'] as $trash => $file)
				$output .= $file ['action'] ." ". $file ['path'] ." \n";
		
		$output .= "\n";
		
		$changelog [] = $output;
	}
	
	if (sizeof ($changelog))
	{
		echo "TITAN CHANGELOG \n";
		echo "===== ========= \n\n";
	
		echo implode ("", $changelog);
	}
}