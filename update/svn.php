<?php

function updateInstanceBySvn ($_path)
{
	try
	{
		ob_start ();

		$_benchmark = time ();

		$sendErrorReport = FALSE;

		echo "\nINFO > Starting auto-update for instance [". $_path ."] (using SVN)... \n";

		if (!function_exists ('svn_ls') || !function_exists ('svn_status'))
			throw new Exception ("CRITICAL > You need install SVN PECL package for PHP!");

		svn_auth_set_parameter (PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, TRUE);
		svn_auth_set_parameter (SVN_AUTH_PARAM_NON_INTERACTIVE, TRUE);
		svn_auth_set_parameter (SVN_AUTH_PARAM_NO_AUTH_CACHE, TRUE);
		svn_auth_set_parameter (SVN_AUTH_PARAM_DONT_STORE_PASSWORDS, TRUE);

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
		 * Clean Up
		 */
		echo "INFO > Cleaning up application folder [". $_folder ."]... \n";

		system (SVN .' cleanup '. $_folder .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --no-auth-cache --non-interactive --trust-server-cert', $return);

		if ($return)
			throw new Exception ("CRITICAL > Impossible to clean up folder [". $_folder ."]! Verify SVN login and password at [configure/titan.xml].");

		echo "SUCCESS > Application folder is cleaned! \n";

		/*
		 * Blacklist treatment
		 */
		echo "INFO > Updating black list of revisions [update/blacklist.txt] to head revision... \n";

		system (SVN .' up '. $_folder .'update'. DIRECTORY_SEPARATOR .'blacklist.txt --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q', $return);

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

		if ($_actualRevision >= $_headRevision)
			throw new Exception ("INFO > File of path [". $_pathToFileOfPaths ."] is in head revision. Update is not necessary!");

		/*
		 * After this point, all erros are send by mail.
		 */
		$sendErrorReport = TRUE;

		$_revertRevision = $_initialRevision = $_actualRevision;

		$_sthUpdateVersion = $_db->prepare ("INSERT INTO ". $_versionTable ." (_version, _author) VALUES (:version, :author)");

		/*
		 * Updating application
		 */
		while ($_actualRevision++ < $_headRevision)
		{
			system (SVN .' up '. $_folder . $_pathToFileOfPaths .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q -r '. $_actualRevision, $return);

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
				!isset ($array [0]['cmt_author']) || trim ($array [0]['cmt_author']) == '' ||
				!isset ($array [0]['cmt_date']) || trim ($array [0]['cmt_date']) == '' || !is_numeric ($array [0]['cmt_date']) || !((int) $array [0]['cmt_date']))
				throw new Exception ("ERROR > Invalid revision number for file [". $_pathToFileOfPaths ."] in work copy! \n". print_r ($array, TRUE) ."\n");

			$_actualRevision = (int) $array [0]['revision'];
			$_commitRevision = (int) $array [0]['cmt_rev'];
			$_authorRevision = $array [0]['cmt_author'];
			$_dateRevision   = (int) $array [0]['cmt_date'];

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

			system (SVN .' up '. $_folder . $_pathToMigrationFiles .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q -r '. $_actualRevision, $return);

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

						system (SVN .' up '. $_folder . $_pathToFileOfPaths .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q -r '. $_revertRevision, $return);

						if ($return)
							echo "CRITICAL > Fail to revert [". $_pathToFileOfPaths ."] to revision #". $_revertRevision ."! Please, access the server to solve problem. \n";

						system (SVN .' up '. $_folder . $_pathToMigrationFiles .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q -r '. $_revertRevision, $return);

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
					{
						echo "INFO > Updating specific migration file to head revision [". $_pathToMigrationFiles . $file .".sql]... \n";

						system (SVN .' up '. $_pathToMigrationFiles . $file .'.sql --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q', $return);

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

					echo "ERROR > Reverting revision for file of paths [". $_pathToFileOfPaths ."] and migration folder [". $_pathToMigrationFiles ."] from #". $_actualRevision ." to #". $_revertRevision ."... \n";

					system (SVN .' up '. $_folder . $_pathToFileOfPaths .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q -r '. $_revertRevision, $return);

					if ($return)
						echo "CRITICAL > Fail to revert [". $_pathToFileOfPaths ."] to revision #". $_revertRevision ."! Please, access the server to solve problem. \n";

					system (SVN .' up '. $_folder . $_pathToMigrationFiles .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q -r '. $_revertRevision, $return);

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

				system (SVN .' up '. $_folder . $file .' --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q -r '. $_actualRevision, $return);

				if ($return)
					echo "ERROR > Fail to update file or folder [". $_folder . $file ."] to revision #". $_actualRevision ."! \n";
				else
					echo "SUCCESS > File or folder [". $_folder . $file ."] updated to revision #". $_actualRevision ."! Setting permission... \n";

				setPermission ($_folder . $file, $_conf ['dir-mode'], $_conf ['file-mode'], $_conf ['owner'], $_conf ['group']);

				echo "INFO > Permission setted recursively to file or folder! \n";
			}

			@file_put_contents ($_cache . DIRECTORY_SEPARATOR .'RELEASE', '; Generated by auto-deploy script at '. date ('Y-m-d H:i:s') ."\n". 'version = '. $_actualRevision ."\n". 'environment = "'. $_conf ['environment'] .'"' ."\n". 'date = '. $_dateRevision ."\n". 'author = "'. $_authorRevision .'"');

			echo "SUCCESS > Files and DB updated to revision #". $_actualRevision ."! \n";

			$_revertRevision = $_actualRevision;

			echo "INFO > Updating VERSION file [update/VERSION] to revision #". $_actualRevision ."! \n";

			system (SVN .' up '. $_folder .'update'. DIRECTORY_SEPARATOR .'VERSION --username "'. $_conf ['svn-login'] .'" --password "'. $_conf ['svn-password'] .'" --accept \'mine-conflict\' --no-auth-cache --non-interactive -q -r '. $_actualRevision, $return);

			if ($return)
				echo "ERROR > Impossible update VERSION file [". $_folder ."update". DIRECTORY_SEPARATOR ."VERSION]! Verify SVN login and password at [configure/titan.xml].";
		}

		if (file_exists ('composer.json'))
		{
			echo "INFO > Installing (or updating) dependencies (with Composer)... \n";

			exec (COMPOSER .' install --no-dev -n');
			exec (COMPOSER .' update --no-dev -n');

			setPermission ('vendor', $_conf ['dir-mode'], $_conf ['file-mode'], $_conf ['owner'], $_conf ['group']);
		}

		echo "FINISH > All done with SUCCESS after ". number_format (time () - $_benchmark, 0, ',', '.') ." seconds! \n\n";

		// printChangelog ($_conf ['changelog'], $_path, $_initialRevision, $_revertRevision, $titanUpdateLog);

		$subject = "[". $_xml ['name'] ." at server ". php_uname ('n') ."] Successful updated to revision #". $_revertRevision ." at ". date ('Y-m-d H:i:s');

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
