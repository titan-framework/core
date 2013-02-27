<?php
/**
 * Script for auto update Titan Framework instances.
 *
 * Copyright 2013 - PLEASE Lab / Embrapa Gado de Corte
 *
 * @author Camilo Carromeu <camilo.carromeu@embrapa.br>
 * @author Jairo Ricardes Rodrigues Filho <jairocgr@gmail.com>
 * @author Bruno Righes <brunorighes@gmail.com>
 * @version 1.0
 * 
 * Main script to update proccess.
 */

error_reporting (E_ALL);
set_time_limit (0);
ini_set ('memory_limit', '-1');
ini_set ('register_argc_argv', '1');

require 'binary.php';
require 'function.php';

$_corePath = dirname (dirname (__FILE__));

require $_corePath . DIRECTORY_SEPARATOR .'class'. DIRECTORY_SEPARATOR .'Xml.php';

set_error_handler ('handleError');

try
{
	if (PHP_SAPI != 'cli')
		throw new Exception ("CRITICAL > This is a command-line script! You cannot call by browser.");
	
	if (PHP_OS != 'Linux')
		throw new Exception ("CRITICAL > This functionality works only in Linux servers (homologated on Debian and Ubuntu).");
	
	if (!(int) ini_get ('register_argc_argv'))
		throw new Exception ("CRITICAL > This is a command-line script! You must enable 'register_argc_argv' directive.");
	
	if ($argc < 2)
		throw new Exception ("CRITICAL > You must pass at least one path for a Titan instance.");
	
	if (!function_exists ('svn_ls') || !function_exists ('svn_status'))
		throw new Exception ("CRITICAL > You need install SVN PECL package for PHP!");
	
	svn_auth_set_parameter (PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, TRUE);
	svn_auth_set_parameter (SVN_AUTH_PARAM_NON_INTERACTIVE, TRUE);
	svn_auth_set_parameter (SVN_AUTH_PARAM_NO_AUTH_CACHE, TRUE);
	svn_auth_set_parameter (SVN_AUTH_PARAM_DONT_STORE_PASSWORDS, TRUE);
	
	if (!function_exists ('system'))
		throw new Exception ("CRITICAL > You need enable OS call functions (verify if PHP is not in safe mode)!");
	
	$commands = array ('SVN', 'GZIP', 'MV', 'SU');
	
	foreach ($commands as $trash => $command)
		if (!defined ($command))
			throw new Exception ("CRITICAL > Configure path for binaries of OS in [". $_corePath . DIRECTORY_SEPARATOR ."update". DIRECTORY_SEPARATOR ."binary.php]! \n");
	
	echo "INFO > Starting auto-update proccess at ". date ('d-m-Y H:i:s') ."... \n";
	
	try
	{
		echo "INFO > Updating Titan Framework... \n";
		
		echo "INFO > Getting last stable revision... \n";
		
		$fileWithStableRevision = $_corePath . DIRECTORY_SEPARATOR .'update'. DIRECTORY_SEPARATOR .'STABLE';
		
		system (SVN .' up '. $fileWithStableRevision .' --no-auth-cache --non-interactive -q', $return);
		
		if ($return || !file_exists ($fileWithStableRevision))
			throw new Exception ("Fail to update file with last stable revision! [". $fileWithStableRevision ."]");
		
		$coreLastStableRevision = (int) file_get_contents ($fileWithStableRevision);
		
		if (is_null ($coreLastStableRevision) || $coreLastStableRevision < 1)
			throw new Exception ("Fail to get last stable revision number! [". $fileWithStableRevision ."]");
		
		$array = svn_ls ('https://svn.cnpgc.embrapa.br/titan');
		
		if (!isset ($array ['core']['created_rev']) || !is_numeric ($array ['core']['created_rev']))
			throw new Exception ("Invalid Titan Framework CORE revision on SVN repository! \n");
		
		$coreLastRevision = (int) $array ['core']['created_rev'];
		
		$array = svn_status ($_corePath, SVN_NON_RECURSIVE|SVN_ALL);
		
		if (!isset ($array [0]['revision']) || !is_numeric ($array [0]['revision']))
			throw new Exception ("Invalid Titan Framework CORE revision on work copy! \n");
		
		$coreActualRevision = (int) $array [0]['revision'];
		
		if ($coreActualRevision != $coreLastStableRevision)
		{
			echo "INFO > Updating (or downgrading) CORE of Titan Framework [". $_corePath ."] from revision #". $coreActualRevision ." to stable revision #". $coreLastStableRevision ." (the last revision in repository is #". $coreLastRevision .")... \n";
			
			system (SVN .' up -r '. $coreLastStableRevision .' '. $_corePath .' --no-auth-cache --non-interactive -q', $return);
			
			if ($return)
				echo "ERROR > Fail to update Titan Framework [". $_corePath ."]! \n";
			else
				echo "SUCCESS > Titan Framework [". $_corePath ."] is updated! \n";
			
			setPermission ($_corePath, octdec ('0775'), octdec ('0664'), 'root', 'staff');
		}
		else
			echo "INFO > Titan Framework is already updated! \n";
	}
	catch (Exception $e)
	{
		echo "ERROR > ". $e->getMessage () ." at line ". $e->getLine () ."! \n";
	}
	
	$_defaultConf = array ( 'environment' => '',
							'svn-login' => '',
							'svn-password' => '',
							'svn-users' => '',
							'backup' => TRUE,
							'file-mode' => '664',
							'dir-mode' => '775',
							'owner' => 'root',
							'group' => 'staff');
	
	for ($i = 1; $i < $argc; $i++)
	{
		if (!file_exists ($argv [$i]) || !is_dir ($argv [$i]))
			continue;
		
		ob_start ();
		
		try
		{
			$_benchmark = time ();
			
			$_path = realpath ($argv [$i]);
			
			echo "\nINFO > Starting auto-update for instance [". $_path ."]... \n";
			
			chdir ($_path);
			
			/*
			 * First, the script open configuration file
			 */
			
			$file = 'configure/titan.xml';
			
			if (!file_exists ($file) || !is_readable ($file))
				throw new Exception ("ERROR > Dont exists a valid instance of Titan and is not possible create a new without file [". $_path . DIRECTORY_SEPARATOR . $file ."]! \n");
			
			$xml = new Xml ($file);
			
			$_xml = $xml->getArray ();
			
			if (!isset ($_xml ['titan-configuration'][0]))
				throw new Exception ("ERROR > The tag 'titan-configuration' dont exist in file [". $_path . DIRECTORY_SEPARATOR . $file ."]! \n");
			
			$_xml = $_xml ['titan-configuration'][0];
			
			echo "INFO > The file 'titan.xml' is loaded! [". $_path . DIRECTORY_SEPARATOR . $file ."] \n";
			
			/*
			 * Verifying prerequisites
			 */
			
			if (!isset ($_xml ['update'][0]['environment']) || trim ($_xml ['update'][0]['environment']) == '')
				throw new Exception ("ERROR > You need set a environment name on tag 'update' of 'titan.xml'! \n");
			
			$_conf = array ();
			
			foreach ($_defaultConf as $key => $value)
				if (array_key_exists ($key, $_xml ['update'][0]) && trim ($_xml ['update'][0][$key]) != '')
					if (is_bool ($value))
						$_conf [$key] = strtoupper (trim ($_xml ['update'][0][$key])) == 'FALSE' ? FALSE : TRUE;
					else
						$_conf [$key] = trim ($_xml ['update'][0][$key]);
				else
					$_conf [$key] = $value;
			
			if (!is_numeric ($_conf ['file-mode']) || strlen ($_conf ['file-mode']) != 3 || !is_numeric ($_conf ['dir-mode']) || strlen ($_conf ['dir-mode']) != 3)
				throw new Exception ("ERROR > You need fix file and folder permissions that will be setted on 'titan.xml' (e.g. 664 and 775)! \n");
			
			$_conf ['file-mode'] = octdec ('0'. $_conf ['file-mode']);
			$_conf ['dir-mode']  = octdec ('0'. $_conf ['dir-mode']);
			
			if ($_conf ['backup'] && (!isset ($_xml ['backup'][0]['path']) || trim ($_xml ['backup'][0]['path']) == '' || !isset ($_xml ['backup'][0]['validity']) || !is_numeric ($_xml ['backup'][0]['validity'])))
				throw new Exception ("ERROR > You need fix backup parameters on tag 'backup' of 'titan.xml'! \n");
		
			/*
			 * Connecting to DB
			 */
			
			if (!isset ($_xml ['database'][0]) || !isset ($_xml ['database'][0]['host']) || !isset ($_xml ['database'][0]['name']))
				throw new Exception ("ERROR > You need configure 'database' on 'titan.xml'! \n");
			
			$_xml ['database'][0]['port'] = isset ($_xml ['database'][0]['port']) && is_numeric ($_xml ['database'][0]['port']) ? trim ($_xml ['database'][0]['port']) : '5432';
			
			if (!in_array ($_xml ['database'][0]['host'], array ('localhost', '127.0.0.1', '::1')) || PHP_OS != 'Linux')
				$dsn = 'pgsql:host='. $_xml ['database'][0]['host'] .' port='. $_xml ['database'][0]['port'] .' dbname='. $_xml ['database'][0]['name'] .' user='. @$_xml ['database'][0]['user'] .' password='. @$_xml ['database'][0]['password'];
			else
				$dsn = 'pgsql:dbname='. $_xml ['database'][0]['name'] .' user='. @$_xml ['database'][0]['user'] .' password='. @$_xml ['database'][0]['password'];
			
			$_db = new PDO ($dsn, @$_xml ['database'][0]['user'], @$_xml ['database'][0]['password']);
			
			$_db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			if (isset ($_xml ['timezone']) && trim ($_xml ['timezone']) != '')
				$_db->exec ("SET timezone TO '". trim ($_xml ['timezone']) ."'");
			
			$schema = isset ($_xml ['database'][0]['schema']) && trim ($_xml ['database'][0]['schema']) != '' ? trim ($_xml ['database'][0]['schema']) : 'public';
			
			$_versionTable = $schema .'._version';
			
			if (!tableExists ($_db, $_versionTable))
				$_db->exec ("CREATE TABLE ". $_versionTable ." (_version CHAR(14) NOT NULL, _author VARCHAR(64) NOT NULL, _date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL, CONSTRAINT _version_pkey PRIMARY KEY(_version))");
			
			/*
			 * Setting additional SVN parameters to PHP SVN library
			 */
			svn_auth_set_parameter (SVN_AUTH_PARAM_DEFAULT_USERNAME, $_conf ['svn-login']);
			svn_auth_set_parameter (SVN_AUTH_PARAM_DEFAULT_PASSWORD, $_conf ['svn-password']);
			
			/*
			 * Setting some global variables
			 */
			$_fileOfPaths = $_conf ['environment'] .'.txt';
			
			$_pathToFileOfPaths = 'update'. DIRECTORY_SEPARATOR .'app'. DIRECTORY_SEPARATOR . $_fileOfPaths;
			
			$_pathToMigrationFiles = 'update'. DIRECTORY_SEPARATOR .'db'. DIRECTORY_SEPARATOR;
			
			$_folder = getcwd () . DIRECTORY_SEPARATOR;
			
			/*
			 * Blacklist treatment
			 */
			echo "INFO > Updating black list of revisions [update/blacklist.txt] to head revision... \n";
				
			system (SVN .' up '. $_folder .'update'. DIRECTORY_SEPARATOR .'blacklist.txt --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --no-auth-cache --non-interactive -q', $return);
			
			if ($return)
				throw new Exception ("CRITICAL > Impossible update black list [". $_folder ."update". DIRECTORY_SEPARATOR ."blacklist.txt]! Verify SVN login and password at [configure/titan.xml].");
			
			if (file_exists ($_folder .'update'. DIRECTORY_SEPARATOR .'blacklist.txt'))
				$_blacklist = file ($_folder .'update'. DIRECTORY_SEPARATOR .'blacklist.txt');
			else
				$_blacklist = array ();
			
			echo "SUCCESS > Black list updated! \n";
			
			/*
			 * Getting head revision to file of paths (file with paths that will be updated)
			 */
			$array = svn_ls ($_pathToFileOfPaths);
				
			if (!isset ($array [$_fileOfPaths]['created_rev']) || trim ($array [$_fileOfPaths]['created_rev']) == '' || !is_numeric ($array [$_fileOfPaths]['created_rev']) || !((int) $array [$_fileOfPaths]['created_rev']))
				throw new Exception ("ERROR > Invalid revision number for file [". $_pathToFileOfPaths ."] in repository! \n". print_r ($array, TRUE) ."\n");
				
			$_headRevision = (int) $array [$_fileOfPaths]['created_rev'];
			
			$_allowedUsers = explode (',', $_conf ['svn-users']);
			
			if (file_exists ($_pathToFileOfPaths))
			{
				$array = svn_status ($_pathToFileOfPaths, SVN_NON_RECURSIVE|SVN_ALL);
					
				if (!is_array ($array) || !isset ($array [0]) || !is_array ($array [0]) || 
					!isset ($array [0]['revision']) || trim ($array [0]['revision']) == '' || !is_numeric ($array [0]['revision']) || !((int) $array [0]['revision']))
					throw new Exception ("ERROR > Invalid revision number for file [". $_pathToFileOfPaths ."] in work copy! \n". print_r ($array, TRUE) ."\n");
				
				$_actualRevision = (int) $array [0]['revision'];
				
				echo "INFO > File of paths [". $_pathToFileOfPaths ."] is at revision #". $_actualRevision ."! It will update to revision #". $_headRevision .". Starting proccess... \n";
			}
			else
			{
				$_actualRevision = 0;
				
				echo "INFO > File of paths [". $_pathToFileOfPaths ."] does not exists! It will update to revision #". $_headRevision .". Starting proccess from revision #1... \n";
			}
			
			if ($_actualRevision == $_headRevision)
				throw new Exception ("INFO > File of path [". $_pathToFileOfPaths ."] is in head revision. Update is not necessary! \n");
			
			$_revertRevision = $_actualRevision;
			
			$_sthUpdateVersion = $_db->prepare ("INSERT INTO ". $_versionTable ." (_version, _author) VALUES (:version, :author)");
			
			/*
			 * Updating application
			 */
			while ($_actualRevision++ < $_headRevision)
			{
				system (SVN .' up '. $_folder . $_pathToFileOfPaths .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --no-auth-cache --non-interactive -q -r '. $_actualRevision, $return);
				
				if ($return)
					throw new Exception ("CRITICAL > Fail to update [". $_pathToFileOfPaths ."] to revision #". $_actualRevision ."!");
				
				echo "SUCCESS > File of paths [". $_pathToFileOfPaths ."] updated to revision #". $_actualRevision ."! \n";
				
				if (!file_exists ($_pathToFileOfPaths))
				{
					echo "INFO > File of paths [". $_pathToFileOfPaths ."] does not exists in this revision! Go to next... \n";
					
					continue;
				}
				
				if (in_array ($_actualRevision, $_blacklist))
				{
					echo "INFO > The revision #". $_actualRevision ." is in black list! Go to next... \n";
					
					continue;
				}
				
				$array = svn_status ($_pathToFileOfPaths, SVN_NON_RECURSIVE|SVN_ALL);
					
				if (!is_array ($array) || !isset ($array [0]) || !is_array ($array [0]) || 
					!isset ($array [0]['revision']) || trim ($array [0]['revision']) == '' || !is_numeric ($array [0]['revision']) || !((int) $array [0]['revision']) ||
					!isset ($array [0]['cmt_rev']) || trim ($array [0]['cmt_rev']) == '' || !is_numeric ($array [0]['cmt_rev']) || !((int) $array [0]['cmt_rev']) ||
					!isset ($array [0]['cmt_author']) || trim ($array [0]['cmt_author']) == '')
					throw new Exception ("ERROR > Invalid revision number for file [". $_pathToFileOfPaths ."] in work copy! \n". print_r ($array, TRUE) ."\n");
				
				$_actualRevision = (int) $array [0]['revision'];
				$_commitRevision = (int) $array [0]['cmt_rev'];
				$_authorRevision = $array [0]['cmt_author'];
				
				if ($_actualRevision != $_commitRevision)
				{
					echo "INFO > This is not a commit revision for file of paths [". $_pathToFileOfPaths ."]! Go to next... \n";
					
					continue;
				}
				
				if (!in_array ($_authorRevision, $_allowedUsers))
				{
					echo "ERROR > Author of revision [". $_authorRevision ."] is not a allowed user! Go to next... \n";
					
					continue;
				}
				
				echo "INFO > This is a commit revision (send by [". $_authorRevision ."])! Starting migration proccess... \n";
				
				echo "INFO > Updating migration folder [". $_folder . $_pathToMigrationFiles ."]... \n";
				
				system (SVN .' up '. $_folder . $_pathToMigrationFiles .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --no-auth-cache --non-interactive -q -r '. $_actualRevision, $return);
				
				if ($return)
					throw new Exception ("CRITICAL > Fail to update migration folder [". $_pathToMigrationFiles ."] to revision #". $_actualRevision ."! \n");
				
				echo "SUCCESS > Migration folder [". $_pathToMigrationFiles ."] updated to revision #". $_actualRevision ."! \n";
				
				echo "INFO > Getting DB version... \n";
				
				$query = $_db->query ("SELECT MAX(_version) AS v FROM ". $_versionTable);
				
				$version = (int) $query->fetchColumn (0);
				
				$dh = opendir ($_pathToMigrationFiles);
				
				if (!$dh)
					throw new Exception ("CRITICAL > Fail to list migration folder [". $_pathToMigrationFiles ."]! \n");
				
				$files = array ();
				
				while (($file = readdir ($dh)) !== false)
				{
					preg_match ('/^(?P<v>\d{14})\.sql/', $file, $m);
					
					if (!is_array ($m) || !isset ($m ['v']))
						continue;
					
					if ($version < (int) $m ['v'])
						$files [] = $m ['v'];
				}
				
				closedir ($dh);
				
				echo "INFO > Has ". sizeof ($files) ." new versions to be applied in DB... \n";
				
				if (sizeof ($files))
				{
					if ($_conf ['backup'])
						try
						{
							backupDatabase ($_xml ['database'][0]['name'], $_xml ['database'][0]['host'], $_xml ['database'][0]['port'], @$_xml ['database'][0]['user'], @$_xml ['database'][0]['password'], trim ($_xml ['backup'][0]['path']), trim ($_xml ['backup'][0]['validity']));
						}
						catch (Exception $e)
						{
							echo $e->getMessage () ." \n";
							
							echo "ERROR > Reverting revision for file of paths [". $_pathToFileOfPaths ."] and migration folder [". $_pathToMigrationFiles ."] from #". $_actualRevision ." to #". $_revertRevision ."... \n";
							
							system (SVN .' up '. $_folder . $_pathToFileOfPaths .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --no-auth-cache --non-interactive -q -r '. $_revertRevision, $return);
							
							if ($return)
								echo "CRITICAL > Fail to revert [". $_pathToFileOfPaths ."] to revision #". $_revertRevision ."! Please, access the server to solve problem. \n";
							
							system (SVN .' up '. $_folder . $_pathToMigrationFiles .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --no-auth-cache --non-interactive -q -r '. $_revertRevision, $return);
							
							if ($return)
								echo "CRITICAL > Fail to revert migration folder [". $_pathToMigrationFiles ."] to revision #". $_revertRevision ."! Please, access the server to solve problem.";
							
							throw new Exception ("CRITICAL > Has a problem with DB backup! Consequently the work copy revision was reversed to #". $_revertRevision .". Contact server admin to fix it! \n");
						}
					
					sort ($files);
					
					reset ($files);
					
					try
					{
						$_db->beginTransaction ();
						
						foreach ($files as $trash => $file)
							$_db->exec (file_get_contents ($_pathToMigrationFiles . $file .'.sql'));
						
						$_sthUpdateVersion->bindParam (':version', $file, PDO::PARAM_STR, 14);
						$_sthUpdateVersion->bindParam (':author', $_authorRevision, PDO::PARAM_STR, 64);
						
						$_sthUpdateVersion->execute ();
						
						$_db->commit ();
						
						echo "SUCCESS > DB is now in version [". $file ."]! \n";
					}
					catch (PDOException $e)
					{
						$_db->rollBack ();
						
						echo "ERROR > Error for apply SQL in DB [". $_pathToMigrationFiles . $file .".sql]: ". $e->getMessage () ." \n";
						
						echo "ERROR > Reverting revision for file of paths [". $_pathToFileOfPaths ."] and migration folder [". $_pathToMigrationFiles ."] from #". $_actualRevision ." to #". $_revertRevision ."... \n";
						
						system (SVN .' up '. $_folder . $_pathToFileOfPaths .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --no-auth-cache --non-interactive -q -r '. $_revertRevision, $return);
						
						if ($return)
							echo "CRITICAL > Fail to revert [". $_pathToFileOfPaths ."] to revision #". $_revertRevision ."! Please, access the server to solve problem. \n";
						
						system (SVN .' up '. $_folder . $_pathToMigrationFiles .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --no-auth-cache --non-interactive -q -r '. $_revertRevision, $return);
						
						if ($return)
							echo "CRITICAL > Fail to revert migration folder [". $_pathToMigrationFiles ."] to revision #". $_revertRevision ."! Please, access the server to solve problem.";
						
						throw new Exception ("CRITICAL > The revision #". $_actualRevision ." has a problem with DB changes and needed to be reversed to #". $_revertRevision .". You can fix it, commit a new revision with new DB changes and add problematic revision to black list [update/blacklist.txt]! \n");
					}
				}
				
				echo "INFO > Starting update of files... \n";
				
				$files = file ($_folder . $_pathToFileOfPaths);
				
				foreach ($files as $trash => $file)
				{
					$file = trim ($file);
					
					if ($file == '')
						continue;
					
					if (strstr ('..', $file) || strstr ('.svn', $file) || $file [0] == DIRECTORY_SEPARATOR || strpos ($file, 'update') === 0 || (file_exists ($file) && (is_link ($file) || realpath ($file) != $_folder . $file)))
					{
						echo "ERROR > File improper to be updated [". $file ."]! \n";
						
						continue;
					}
				
					system (SVN .' up '. $_folder . $file .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --no-auth-cache --non-interactive -q -r '. $_actualRevision, $return);
							
					if ($return)
						echo "ERROR > Fail to update file or folder [". $_folder . $file ."] to revision #". $_actualRevision ."! \n";
					else
						echo "SUCCESS > File or folder [". $_folder . $file ."] updated to revision #". $_actualRevision ."! Setting permission... \n";
					
					setPermission ($_folder . $file, $_conf ['dir-mode'], $_conf ['file-mode'], $_conf ['owner'], $_conf ['group']);
					
					echo "INFO > Permission setted recursively to file or folder! \n";
				}
				
				echo "SUCCESS > Files and DB updated to revision #". $_actualRevision ."! \n";
				
				$_revertRevision = $_actualRevision;
			}
			
			echo "FINISH > All done after ". number_format (time () - $_benchmark, 0, ',', '.') ." seconds! \n";
			
			$subject = "[". $_xml ['name'] ." at server ". php_uname ('n') ."] Successful updated to revision #". $_revertRevision ." at ". date ('Y-m-d H:i');
			
			$buffer = ob_get_clean ();
			
			@mail (@$_xml ['e-mail'], $subject, $buffer);
			
			echo $buffer;
		}
		catch (PDOException $e)
		{
			echo ob_get_clean ();
			
			echo "CRITICAL > Error in DB connection! [". $e->getMessage () ." at line ". $e->getLine () ."] \n";
		}
		catch (Exception $e)
		{
			echo ob_get_clean ();
			
			echo $e->getMessage ();
		}
	}
}
catch (Exception $e)
{
	echo $e->getMessage () ." \n";
	
	echo "FINISH > Critical error after ". number_format (time () - $_benchmark, 0, ',', '.') ." seconds! \n";
}