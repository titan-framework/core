<?php
/**
 * Script to update database of standard Titan Framework instance.
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

echo "\n";

echo "Starting Titan Framework's installation tool... \n\n";

try
{
	if (PHP_SAPI != 'cli')
		throw new Exception ("[ERROR] This is a command-line script! You cannot call by browser.");

	if (!(int) ini_get ('register_argc_argv'))
		throw new Exception ("[ERROR] This is a command-line script! You must enable 'register_argc_argv' directive.");

	if (!function_exists ('system') || !function_exists ('exec'))
		throw new Exception ("[ERROR] You need enable OS call functions (verify if PHP is not in safe mode)!");

	if ($argc < 2)
		throw new Exception ("[ERROR] The correct formart of command is:\nphp path/to/core/update/db.php path/to/instance [--ignore-errors]");

	if (!file_exists ($argv [1]) || !is_dir ($argv [1]))
		throw new Exception ("[ERROR] Instance directory not appear a Titan application!");

	$_path = realpath ($argv [1]);

	$_ignore = FALSE;

	if ($argc == 3 && $argv [2] == '--ignore-errors')
		$_ignore = TRUE;

	chdir ($_path);

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
	 * Connecting to DB
	 */

	if (!isset ($_xml ['database'][0]) || !isset ($_xml ['database'][0]['host']) || !isset ($_xml ['database'][0]['name']))
		throw new Exception ("[ERROR] You need configure 'database' on 'titan.xml'!");

	$_xml ['database'][0]['port'] = isset ($_xml ['database'][0]['port']) && is_numeric ($_xml ['database'][0]['port']) ? trim ($_xml ['database'][0]['port']) : '5432';

	if (!in_array ($_xml ['database'][0]['host'], array ('localhost', '127.0.0.1', '::1')))
		$dsn = 'pgsql:host='. $_xml ['database'][0]['host'] .' port='. $_xml ['database'][0]['port'] .' dbname='. $_xml ['database'][0]['name'] .' user='. @$_xml ['database'][0]['user'] .' password='. @$_xml ['database'][0]['password'];
	else
		$dsn = 'pgsql:dbname='. $_xml ['database'][0]['name'] .' user='. @$_xml ['database'][0]['user'] .' password='. @$_xml ['database'][0]['password'];

	$dbUser = @$_xml ['database'][0]['user'];
	$dbPass = @$_xml ['database'][0]['password'];
	$dbName = @$_xml ['database'][0]['name'];
	$dbPort = @$_xml ['database'][0]['port'];
	$dbHost = @$_xml ['database'][0]['host'];

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

		echo "[INFO] Has ". sizeof ($files) ." new versions to be applied in DB... \n\n";

		if (sizeof ($files))
		{
			sort ($files);

			reset ($files);

			$_sthUpdateVersion = $_db->prepare ("INSERT INTO ". $_versionTable ." (_version, _author) VALUES (:version, 'DB Migration Update Script')");

			foreach ($files as $trash => $file)
				try
				{
					$_db->beginTransaction ();

					echo "[INFO] Trying to apply migration file [". $_pathToMigrationFiles . $file .".sql]... \n";

					$sql = file_get_contents ($_pathToMigrationFiles . $file .'.sql');

					if (trim ($sql) != '')
						$_db->exec ($sql);

					$_sthUpdateVersion->bindParam (':version', $file, PDO::PARAM_STR, 14);

					$_sthUpdateVersion->execute ();

					$_db->commit ();

					echo "[SUCCESS] DB is now in version [". $file ."]! \n\n";
				}
				catch (PDOException $e)
				{
					$_db->rollBack ();

					$error = "[CRITICAL] Error for apply SQL in DB [". $_pathToMigrationFiles . $file .".sql]: ". $e->getMessage ();

					if ($_ignore)
						echo $error ."\n\n";
					else
						throw new Exception ($error);
				}
		}
	}

	echo "[SUCCESS] All done! \n\n";
}
catch (Exception $e)
{
	echo $e->getMessage () ." \n";
}

echo "\n";
