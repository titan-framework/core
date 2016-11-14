<?php

function updateInstanceByGit ($_path)
{
	try
	{
		ob_start ();

		$_benchmark = time ();

		$sendErrorReport = FALSE;

		echo "\nINFO > Starting auto-update for instance [". $_path ."] (using GIT)... \n";

		if (!`which git`)
			throw new Exception ("CRITICAL > You need install GIT package (try 'apt-get install git')!");

		chdir ($_path);

		$_conf = array ();

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

		if (isset ($_xml ['url']) && trim ($_xml ['url']) != '')
			echo "INFO > This instance is located at [". $_xml ['url'] ."] \n";

		if (isset ($_xml ['timezone']) && trim ($_xml ['timezone']) != '')
			date_default_timezone_set (trim ($_xml ['timezone']));

		/*
		 * Verifying prerequisites
		 */

		if (!isset ($_xml ['cache-path']) || trim ($_xml ['cache-path']) == '' || !is_dir ($_xml ['cache-path']) || !is_writable ($_xml ['cache-path']))
			throw new Exception ("ERROR > You need set a writable cache folder on tag 'titan-configuration' of 'titan.xml'! \n");

		$_cache = realpath ($_xml ['cache-path']);

		if (!isset ($_xml ['update'][0]['environment']) || trim ($_xml ['update'][0]['environment']) == '')
			throw new Exception ("ERROR > You need set a environment name on tag 'update' of 'titan.xml'! \n");

		$_defaultConf = array (
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

		$_conf ['changelog'] = strtoupper ($_conf ['changelog']);

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

		$query = $_db->query ("SELECT u._email FROM ". $schema ."._user u JOIN ". $schema ."._user_group ug ON ug._user = u._id JOIN ". $schema ."._group g ON g._id = ug._group WHERE g._admin = B'1'");

		$_mails = $query->fetchAll (PDO::FETCH_COLUMN);

		if (!sizeof ($_mails))
			$_mails = array (@$_xml ['e-mail']);

		/*
		 * Setting some global variables
		 */
		$_branch = $_conf ['environment'];

		$_pathToMigrationFiles = 'update'. DIRECTORY_SEPARATOR .'db'. DIRECTORY_SEPARATOR;

		$_folder = getcwd () . DIRECTORY_SEPARATOR;

		if (array_key_exists ('e-mail', $_xml) && trim ($_xml ['e-mail']) != '')
			exec (GIT .' config user.email "'. $_xml ['e-mail'] .'"');
		else
			exec (GIT .' config user.email "auto-deploy@titanframework.com"');

		exec (GIT .' config user.name "Titan Auto-Deploy Script"');

		/*
		 * Getting local TAG
		 */
		exec (GIT .' describe --tags', $out);

		if (!is_array ($out) || !array_key_exists (0, $out) || preg_replace ('/[^0-9\.\-]/i', '', $out [0]) == '')
			throw new Exception ("Impossible to get last version of instance work copy! Please, verify if Git is installed and the health of work copy.");

		$_actual = trim ($out [0]);

		unset ($out);

		/*
		 * Getting last TAG on remote repository
		 */
		exec (GIT .' fetch --all');

		exec (GIT .' describe --abbrev=0 --tags origin/'. $_branch, $out);

		if (!is_array ($out) || !array_key_exists (0, $out) || preg_replace ('/[^0-9\.\-]/i', '', $out [0]) == '')
			throw new Exception ("Impossible to get last version of instance on remote repository! Please, verify if Git is installed and if branch [". $_branch ."] has TAGs.");

		$_last = trim ($out [0]);

		if ($_last == $_actual)
			throw new Exception ("INFO > Work copy in same version of remote repository [". $_last ."]. Update is not necessary!");

		/*
		 * After this point, all erros are send by mail.
		 */
		$sendErrorReport = TRUE;

		$_sthUpdateVersion = $_db->prepare ("INSERT INTO ". $_versionTable ." (_version, _author) VALUES (:version, :author)");

		/*
		 * Updating work copy to last TAG
		 */

		echo "INFO > Updating application files... \n";

		exec (GIT .' stash');

		exec (GIT .' checkout origin/'. $_branch);

		exec (GIT .' pull origin '. $_branch);

		exec (GIT .' checkout '. $_last);

		exec (GIT .' stash apply');

		unset ($out);

		exec (GIT .' diff --name-only '. $_last .' '. $_actual, $out);

		if (is_array ($out) && sizeof ($out))
			foreach ($out as $trash => $file)
			{
				echo "INFO > Setting permission to file [". $file ."]... \n";

				setPermission ($file, $_conf ['dir-mode'], $_conf ['file-mode'], $_conf ['owner'], $_conf ['group']);
			}

		echo "INFO > Permission setted to modified files! \n";

		echo "SUCCESS > Application folder updated to version [". $_last ."]! \n";

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
		 * Updating database to last TAG
		 */

		echo "INFO > Updating database... \n";

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

					echo "ERROR > Roll back application files from version ". $_last ." to ". $_actual ."... \n";

					gitRollBack ($_actual);

					throw new Exception ("CRITICAL > Has a problem with DB backup! Consequently the work copy revision was reversed to version ". $_actual .". Contact server admin to fix it! \n");
				}

			sort ($files);

			reset ($files);

			try
			{
				$_db->beginTransaction ();

				foreach ($files as $trash => $file)
				{
					echo "INFO > Updating specific migration file to head revision [". $_pathToMigrationFiles . $file .".sql]... \n";

					system (GIT .' checkout origin/'. $_branch .' -- '. $_pathToMigrationFiles . $file .'.sql', $return);

					if ($return)
						throw new PDOException ("CRITICAL > Fail to update specifc migration file [". $_pathToMigrationFiles . $file .".sql] to head revision! \n");

					if (file_exists ($_pathToMigrationFiles . $file .'.sql'))
					{
						echo "SUCCESS > Migration file [". $_pathToMigrationFiles . $file .".sql] updated to head revision! \n";

						$sql = file_get_contents ($_pathToMigrationFiles . $file .'.sql');

						if (trim ($sql) != '')
							$_db->exec ($sql);
					}
					else
						echo "SUCCESS > Migration file [". $_pathToMigrationFiles . $file .".sql] deleted! \n";
				}

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

				echo "ERROR > Reverting application files from version ". $_last ." to ". $_actual ."... \n";

				gitRollBack ($_actual);

				throw new Exception ("CRITICAL > The tag ". $_last ." of branch origin/". $_branch ." has a problem with DB changes and needed to be reversed to version ". $_actual ."! \n");
			}
		}

		echo "SUCCESS > Files and DB updated to version ". $_last ."! \n";

		echo "INFO > Updating information about application version at files [update/VERSION] and [cache/RELEASE]... \n";

		$aux = explode ('-', preg_replace ('/[^0-9\.\-]/i', '', $_last));

		if (array_key_exists (0, $aux) && trim ($aux [0]) != '')
			@file_put_contents ('update'. DIRECTORY_SEPARATOR .'VERSION', $aux [0]);

		$release = '';
		if (array_key_exists (1, $aux) && trim ($aux [1]) != '')
			$release = trim ($aux [1]);

		@file_put_contents ($_cache . DIRECTORY_SEPARATOR .'RELEASE', '; Generated by auto-deploy script at '. date ('Y-m-d H:i:s') ."\n". 'version = '. $release ."\n". 'environment = "'. $_conf ['environment'] .'"' ."\n". 'date = '. $_dateRevision ."\n". 'author = "'. $_authorRevision .'"');

		echo "FINISH > All done with SUCCESS after ". number_format (time () - $_benchmark, 0, ',', '.') ." seconds! \n\n";

		$subject = "[". $_xml ['name'] ." at server ". php_uname ('n') ."] Successful updated to version ". preg_replace ('/[^0-9\.\-]/i', '', $_last) ." at ". date ('Y-m-d H:i:s');

		$buffer = ob_get_clean ();

		@mail (implode (',', $_mails), '=?utf-8?B?'. base64_encode ($subject) .'?=', $buffer, "From: ". @$_xml ['e-mail'] ."\r\nContent-Type: text/plain; charset=utf-8");

		echo $buffer;
	}
	catch (PDOException $e)
	{
		echo ob_get_clean ();

		echo "CRITICAL > Error in DB connection! [". $e->getMessage () ." at line ". $e->getLine () ."] \n";
	}
	catch (Exception $e)
	{
		echo $e->getMessage () ."\n";

		echo "FINISH > Stopped after ". number_format (time () - $_benchmark, 0, ',', '.') ." seconds! \n\n";

		// TODO: Send log if error occurs.
		// if (isset ($_path) && isset ($_conf ['changelog']) && isset ($_initialRevision) && isset ($_revertRevision))
		//	printChangelog ($_conf ['changelog'], $_path, $_initialRevision, $_revertRevision, $titanUpdateLog);

		$buffer = ob_get_clean ();

		if ($sendErrorReport)
		{
			$subject = "[". $_xml ['name'] ." at server ". php_uname ('n') ."] CRITICAL ERROR to update system at ". date ('Y-m-d H:i');

			@mail (isset ($_mails) ? implode (',', $_mails) : @$_xml ['e-mail'], '=?utf-8?B?'. base64_encode ($subject) .'?=', $buffer, "From: ". @$_xml ['e-mail'] ."\r\nContent-Type: text/plain; charset=utf-8");
		}

		echo $buffer;
	}
}

function gitRollBack ($version)
{
	exec (GIT .' stash');

	exec (GIT .' checkout '. $version);

	exec (GIT .' checkout stash -- .');
}
