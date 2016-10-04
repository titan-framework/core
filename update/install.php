<?php
/**
 * Script to install a standard Titan Framework instance
 * from GIT repository.
 *
 * Copyright 2016: PLEASE Lab / Embrapa Gado de Corte
 *
 * @author Camilo Carromeu <camilo.carromeu@embrapa.br>
 * @version 1.0
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

 echo "\n";

 echo "Starting Titan Framework's installation tool... \n\n";

 try
 {
 	if (PHP_SAPI != 'cli')
 		throw new Exception ("[ERROR] This is a command-line script! You cannot call by browser.");

 	if (PHP_OS != 'Linux')
 		throw new Exception ("[ERROR] This functionality works only in Linux servers (homologated on Debian and Ubuntu).");

 	if (!(int) ini_get ('register_argc_argv'))
 		throw new Exception ("[ERROR] This is a command-line script! You must enable 'register_argc_argv' directive.");

 	if (!function_exists ('system') || !function_exists ('exec'))
 		throw new Exception ("[ERROR] You need enable OS call functions (verify if PHP is not in safe mode)!");

	if (!`which git`)
		throw new Exception ("[ERROR] You need install GIT package (try 'apt-get install git')!");

 	$commands = array ('SVN', 'GZIP', 'MV', 'SU', 'GIT');

 	foreach ($commands as $trash => $command)
 		if (!defined ($command))
 			throw new Exception ("[ERROR] Configure path for binaries of OS in [". $_corePath . DIRECTORY_SEPARATOR ."update". DIRECTORY_SEPARATOR ."binary.php]! \n");

	if ($argc < 4)
		throw new Exception ("[ERROR] The correct formart of command is:\nphp path/to/core/update/install.php git@your.git.host.com:group/repository.git path/where/will/install/instance branch-name");

	$_repos = trim ($argv [1]);

	$_branch = trim ($argv [3]);

	if (!file_exists ($argv [2]) || !is_dir ($argv [2]))
		exec ('mkdir -p '. $argv [2], $trash);

	$_path = realpath ($argv [2]);

	/*
	 * Cloning repos in last tag
	 */

	exec (GIT .' clone --branch '. $_branch .' '. $_repos .' '. $_path, $trash);

	chdir ($_path);

	exec (GIT .' fetch --all', $trash);

	exec (GIT .' describe --abbrev=0 --tags origin/'. $_branch, $out);

	if (!is_array ($out) || !array_key_exists (0, $out) || preg_replace ('/[^0-9\.\-]/i', '', $out [0]) == '')
		throw new Exception ("Impossible to get last version of instance on remote repository! Please, verify if Git is installed and if branch [". $_branch ."] has TAGs.");

	$_last = trim ($out [0]);

	exec (GIT .' checkout origin/'. $_branch, $trash);

	exec (GIT .' pull origin '. $_branch, $trash);

	exec (GIT .' checkout '. $_last, $trash);

	echo "[SUCCESS] Work copy created at [". $_path ."] with last version of repository [". $_last ."]! \n\n";

	unset ($out);

	exec (GIT ." log -1 --format='%ai#%an' ". $_last, $out);

	$_authorRevision = '';
	$_dateRevision   = time ();

	if (is_array ($out) && array_key_exists (0, $out))
	{
		$aux = explode ('#', trim ($out [0]));

		$_authorRevision = @$aux [1];
		$_dateRevision = strtotime (@$aux [0]);
	}

	/*
	 * Open configuration file
	 */

	$file = 'configure/titan.xml';

	if (!file_exists ($file) || !is_readable ($file))
		throw new Exception ("[ERROR] Dont exists a valid instance of Titan and is not possible create a new without file [". $_path . DIRECTORY_SEPARATOR . $file ."]! \n");

	$xml = new Xml ($file);

	$_xml = $xml->getArray ();

	if (!isset ($_xml ['titan-configuration'][0]))
		throw new Exception ("[ERROR] The tag 'titan-configuration' dont exist in file [". $_path . DIRECTORY_SEPARATOR . $file ."]! \n");

	$_xml = $_xml ['titan-configuration'][0];

	echo "[INFO] The file 'titan.xml' is loaded! [". $_path . DIRECTORY_SEPARATOR . $file ."] \n";

	if (isset ($_xml ['url']) && trim ($_xml ['url']) != '')
		echo "[INFO] This instance is located at [". $_xml ['url'] ."] \n";

	if (isset ($_xml ['timezone']) && trim ($_xml ['timezone']) != '')
		date_default_timezone_set (trim ($_xml ['timezone']));

	/*
	 * Verifying prerequisites
	 */

	if (!isset ($_xml ['cache-path']) || trim ($_xml ['cache-path']) == '')
 		throw new Exception ("[ERROR] You need set a cache folder on tag 'titan-configuration' of 'titan.xml'!");

	exec ('mkdir -p '. $_xml ['cache-path'], $trash);

	$_cache = realpath ($_xml ['cache-path']);

	if (!isset ($_xml ['archive'][0]['data-path']) || trim ($_xml ['archive'][0]['data-path']) == '')
 		throw new Exception ("[ERROR] You need set a folder to file upload on tag 'archive' of 'titan.xml'!");

	exec ('mkdir -p '. $_xml ['archive'][0]['data-path'], $trash);

	$_conf = array (
		'environment' => '',
		'svn-login' => '',
		'svn-password' => '',
		'svn-users' => '',
		'backup' => TRUE,
		'file-mode' => '664',
		'dir-mode' => '775',
		'owner' => 'root',
		'group' => 'staff',
		'changelog' => 'DEFAULT'
	);

	if (array_key_exists ('update', $_xml))
	{
		foreach ($_conf as $key => $value)
			if (array_key_exists ($key, $_xml ['update'][0]) && trim ($_xml ['update'][0][$key]) != '')
			{
				if (is_bool ($value))
					$_conf [$key] = strtoupper (trim ($_xml ['update'][0][$key])) == 'FALSE' ? FALSE : TRUE;
				else
					$_conf [$key] = trim ($_xml ['update'][0][$key]);
			}
	}

	if (!is_numeric ($_conf ['file-mode']) || strlen ($_conf ['file-mode']) != 3 || !is_numeric ($_conf ['dir-mode']) || strlen ($_conf ['dir-mode']) != 3)
		throw new Exception ("[ERROR] You need fix file and folder permissions that will be setted on 'titan.xml' (e.g. 664 and 775)!");

	$_conf ['file-mode'] = octdec ('0'. $_conf ['file-mode']);
	$_conf ['dir-mode']  = octdec ('0'. $_conf ['dir-mode']);

	if ($_conf ['backup'] && (!isset ($_xml ['backup'][0]['path']) || trim ($_xml ['backup'][0]['path']) == '' || !isset ($_xml ['backup'][0]['validity']) || !is_numeric ($_xml ['backup'][0]['validity'])))
		throw new Exception ("[ERROR] You need fix backup parameters on tag 'backup' of 'titan.xml'!");

	exec ('mkdir -p '. $_xml ['backup'][0]['path'], $trash);

	$_conf ['changelog'] = strtoupper ($_conf ['changelog']);

	/*
	 * Registring version and release
	 */

	$aux = explode ('-', preg_replace ('/[^0-9\.\-]/i', '', $_last));

	if (array_key_exists (0, $aux) && trim ($aux [0]) != '')
		@file_put_contents ('update'. DIRECTORY_SEPARATOR .'VERSION', $aux [0]);

	$release = '';
	if (array_key_exists (1, $aux) && trim ($aux [1]) != '')
		$release = trim ($aux [1]);

	@file_put_contents ($_cache . DIRECTORY_SEPARATOR .'RELEASE', '; Generated by auto-deploy script at '. date ('Y-m-d H:i:s') ."\n". 'version = '. $release ."\n". 'environment = "'. $_conf ['environment'] .'"' ."\n". 'date = '. $_dateRevision ."\n". 'author = "'. $_authorRevision .'"');

	/*
	 * Setting permissions
	 */

	setPermission ($_path, $_conf ['dir-mode'], $_conf ['file-mode'], $_conf ['owner'], $_conf ['group']);

	setPermission ($_cache, octdec ('0755'), octdec ('0644'), 'www-data', 'staff');

	setPermission ($_xml ['archive'][0]['data-path'], octdec ('0755'), octdec ('0644'), 'www-data', 'staff');

	if ($_conf ['backup'])
		setPermission ($_xml ['backup'][0]['path'], octdec ('0755'), octdec ('0644'), 'www-data', 'staff');

	echo "[INFO] Permission setted recursively to work copy [". $_path ."]! \n\n";

	/*
	 * Connecting to DB
	 */

	if (!isset ($_xml ['database'][0]) || !isset ($_xml ['database'][0]['host']) || !isset ($_xml ['database'][0]['name']))
		throw new Exception ("[ERROR] You need configure 'database' on 'titan.xml'!");

	$_xml ['database'][0]['port'] = isset ($_xml ['database'][0]['port']) && is_numeric ($_xml ['database'][0]['port']) ? trim ($_xml ['database'][0]['port']) : '5432';

	if (!in_array ($_xml ['database'][0]['host'], array ('localhost', '127.0.0.1', '::1')))
		$dsn = 'pgsql:host='. $_xml ['database'][0]['host'] .' port='. $_xml ['database'][0]['port'] .' dbname='. $_xml ['database'][0]['name'] .' user='. @$_xml ['database'][0]['user'] .' password='. @$_xml ['database'][0]['password'];
	else
		$dsn = 'pgsql:dbname='. $_xml ['database'][0]['name'] .' user='. @$_xml ['database'][0]['user'] .' password='. @$_xml ['database'][0]['password'];

	echo "To connect on database, the bottom DSN will be used. Please, verify and press ENTER if is correct. If not, enter with correct DSN bellow... \n";
	echo "Use '". $dsn ."' or type a alternative: \n";

	$input = fgets (fopen ('php://stdin', 'r'));

	if (trim ($input) == '')
	{
		$dbUser = @$_xml ['database'][0]['user'];
		$dbPass = @$_xml ['database'][0]['password'];
		$dbName = @$_xml ['database'][0]['name'];
		$dbPort = @$_xml ['database'][0]['port'];
		$dbHost = @$_xml ['database'][0]['host'];
	}
	else
	{
		$dsn = trim ($input);

		while (strpos ($dsn, '  ') !== FALSE)
			$dsn = str_replace ('  ', ' ', $dsn);

		$params = explode (' ', substr ($dsn, 6));

		$dbUser = $dbPass = $dbName = '';

		$dbHost = 'localhost';
		$dbPort = '5432';

		foreach ($params as $trash => $param)
		{
			$aux = explode ('=', $param);

			switch ($aux [0])
			{
				case 'user':
					$dbUser = $aux [1];
					break;

				case 'password':
					$dbPass = $aux [1];
					break;

				case 'host':
					$dbHost = $aux [1];
					break;

				case 'port':
					$dbPort = $aux [1];
					break;

				case 'dbname':
					$dbName = $aux [1];
					break;
			}
		}
	}

	if (in_array ($dbHost, array ('localhost', '127.0.0.1', '::1')) && !`su - postgres -c "psql -tAc \"SELECT 1 FROM pg_database where datname = '$dbName';\""`)
	{
		echo "\n";
		echo "Do not exists a database named '$dbName' in this server. You want to create it? (yes/no): ";

		$input = fgets (fopen ('php://stdin', 'r'));

		if (trim ($input) == 'yes')
		{
			if (!file_exists ('db'. DIRECTORY_SEPARATOR .'last.sql'))
				throw new Exception ("[CRITICAL] To install instance is necessary a project with standard folder structure. Thus, is needed a DUMP of initial database data and structure in file [". $_path ."/db/last.sql], but this file does not exists!");

			if (!`su - postgres -c "psql -tAc \"SELECT 1 FROM pg_roles WHERE rolname = '$dbUser';\""`)
				exec ('su - postgres -c "psql -c \"CREATE ROLE '. $dbUser .' WITH LOGIN ENCRYPTED PASSWORD \''. $dbPass .'\';\""', $trash);

			exec ('su - postgres -c "createdb -E utf8 -O '. $dbUser .' -T template0 '. $dbName .'"', $trash);

			exec ('su - postgres -c "psql -d '. $dbName .' -c \"CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;\""', $trash);

			exec ('su - postgres -c "psql -d '. $dbName .' -U '. $dbUser .' < '. $_path . DIRECTORY_SEPARATOR .'db'. DIRECTORY_SEPARATOR .'last.sql"', $trash);
		}
	}

	$_db = new PDO ($dsn, $dbUser, $dbPass);

	$_db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	if (isset ($_xml ['timezone']) && trim ($_xml ['timezone']) != '')
		$_db->exec ("SET timezone TO '". trim ($_xml ['timezone']) ."'");

	$schema = isset ($_xml ['database'][0]['schema']) && trim ($_xml ['database'][0]['schema']) != '' ? trim ($_xml ['database'][0]['schema']) : 'public';

	$_versionTable = $schema .'._version';

	if (!tableExists ($_db, $_versionTable))
		$_db->exec ("CREATE TABLE ". $_versionTable ." (_version CHAR(14) NOT NULL, _author VARCHAR(64) NOT NULL, _date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL, CONSTRAINT _version_pkey PRIMARY KEY(_version))");

	/*
	 * Updating database to last version
	 */

	$_pathToMigrationFiles = 'update'. DIRECTORY_SEPARATOR .'db'. DIRECTORY_SEPARATOR;

	if (file_exists ($_pathToMigrationFiles) && is_dir ($_pathToMigrationFiles))
	{
		$query = $_db->query ("SELECT MAX(_version) AS v FROM ". $_versionTable);

		$version = (int) $query->fetchColumn (0);

		$dh = opendir ($_pathToMigrationFiles);

		if (!$dh)
			throw new Exception ("[CRITICAL] Fail to list migration folder [". $_pathToMigrationFiles ."]!");

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

		echo "[INFO] Has ". sizeof ($files) ." new versions to be applied in DB... \n";

		if (sizeof ($files))
		{
			sort ($files);

			reset ($files);

			try
			{
				$_db->beginTransaction ();

				foreach ($files as $trash => $file)
				{
					echo "[INFO] Updating specific migration file to head revision [". $_pathToMigrationFiles . $file .".sql]... \n";

					system (GIT .' checkout origin/'. $_branch .' -- '. $_pathToMigrationFiles . $file .'.sql', $return);

					if ($return)
						throw new PDOException ("[CRITICAL] Fail to update specifc migration file [". $_pathToMigrationFiles . $file .".sql] to head revision!");

					if (file_exists ($_pathToMigrationFiles . $file .'.sql'))
					{
						echo "[SUCCESS] Migration file [". $_pathToMigrationFiles . $file .".sql] updated to head revision! \n";

						$sql = file_get_contents ($_pathToMigrationFiles . $file .'.sql');

						if (trim ($sql) != '')
							$_db->exec ($sql);
					}
					else
						echo "[SUCCESS] Migration file [". $_pathToMigrationFiles . $file .".sql] deleted! \n\n";
				}

				$_sthUpdateVersion = $_db->prepare ("INSERT INTO ". $_versionTable ." (_version, _author) VALUES (:version, :author)");

				$_sthUpdateVersion->bindParam (':version', $file, PDO::PARAM_STR, 14);
				$_sthUpdateVersion->bindParam (':author', $_authorRevision, PDO::PARAM_STR, 64);

				$_sthUpdateVersion->execute ();

				$_db->commit ();

				echo "[SUCCESS] DB is now in version [". $file ."]! \n\n";
			}
			catch (PDOException $e)
			{
				$_db->rollBack ();

				throw new Exception ("[CRITICAL] Error for apply SQL in DB [". $_pathToMigrationFiles . $file .".sql]: ". $e->getMessage ());
			}
		}
	}

	echo "[SUCCESS] All done! \n\n";

	echo "[WARNING] You still need configure your instance! Please, edit configuration files with correct parameters (like [". $_path ."/configure/titan.xml]). \n";
}
catch (Exception $e)
{
	echo $e->getMessage () ." \n";
}

echo "\n";
