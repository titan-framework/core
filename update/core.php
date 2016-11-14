<?php

function updateCoreBySvn ($_path)
{
	echo "WARNING > Your Titan Framework refers the old Subversion repository! Please, get a new workcopy for Titan Framework's Core on https://github.com/titan-framework/install. \n";

	if (!function_exists ('svn_ls') || !function_exists ('svn_status'))
		throw new Exception ("CRITICAL > You need install SVN PECL package for PHP!");

	svn_auth_set_parameter (PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, TRUE);
	svn_auth_set_parameter (SVN_AUTH_PARAM_NON_INTERACTIVE, TRUE);
	svn_auth_set_parameter (SVN_AUTH_PARAM_NO_AUTH_CACHE, TRUE);
	svn_auth_set_parameter (SVN_AUTH_PARAM_DONT_STORE_PASSWORDS, TRUE);

	echo "INFO > Cleaning up CORE folder [". $_path ."]... \n";

	system (SVN .' cleanup '. $_path .' --no-auth-cache --non-interactive --trust-server-cert', $return);

	if ($return)
		echo "ERROR > Impossible to clean up CORE folder [". $_path ."]! \n";
	else
		echo "SUCCESS > Titan Framework's CORE folder is cleaned! \n";

	echo "INFO > Getting last stable revision... \n";

	$fileWithStableRevision = $_path . DIRECTORY_SEPARATOR .'update'. DIRECTORY_SEPARATOR .'STABLE';

	system (SVN .' up '. $fileWithStableRevision .' --accept \'mine-conflict\' --no-auth-cache --non-interactive --trust-server-cert -q', $return);

	if ($return || !file_exists ($fileWithStableRevision))
		throw new Exception ("Fail to update file with last stable revision! [". $fileWithStableRevision ."]");

	$coreLastStableRevision = (int) file_get_contents ($fileWithStableRevision);

	if (is_null ($coreLastStableRevision) || $coreLastStableRevision < 1)
		throw new Exception ("Fail to get last stable revision number! [". $fileWithStableRevision ."]");

	$array = svn_ls ('https://svn.cnpgc.embrapa.br/titan');

	if (!isset ($array ['core']['created_rev']) || !is_numeric ($array ['core']['created_rev']))
		throw new Exception ("Invalid Titan Framework CORE revision on SVN repository! \n");

	$coreLastRevision = (int) $array ['core']['created_rev'];

	$array = svn_status ($_path, SVN_NON_RECURSIVE|SVN_ALL);

	if (!isset ($array [0]['revision']) || !is_numeric ($array [0]['revision']))
		throw new Exception ("Invalid Titan Framework CORE revision on work copy! \n");

	$coreActualRevision = (int) $array [0]['revision'];

	if ($coreActualRevision != $coreLastStableRevision)
	{
		echo "INFO > Updating (or downgrading) CORE of Titan Framework [". $_path ."] from revision #". $coreActualRevision ." to stable revision #". $coreLastStableRevision ." (the last revision in repository is #". $coreLastRevision .")... \n";

		system (SVN .' up -r '. $coreLastStableRevision .' '. $_path .' --accept \'mine-conflict\' --no-auth-cache --non-interactive --trust-server-cert -q', $return);

		if ($return)
			echo "ERROR > Fail to update Titan Framework [". $_path ."]! \n";
		else
			echo "SUCCESS > Titan Framework [". $_path ."] is updated! \n";

		setPermission ($_path, octdec ('0775'), octdec ('0664'), 'root', 'staff');

		// TODO: Send log from SVN by e-mail.
		// if ($coreActualRevision < $coreLastStableRevision)
		//	return svn_log ($_path, $coreActualRevision + 1, $coreLastStableRevision);
	}
	else
		echo "INFO > Titan Framework is already updated! \n";
}

function updateCoreByGit ($_path)
{
	echo "INFO > Your Titan Framework's Core refers GitHub repository. Donuts for you ;-) \n";

	chdir ($_path);

	unset ($out);

	exec (GIT .' describe --abbrev=0 --tags', $out);

	if (!is_array ($out) || !array_key_exists (0, $out) || preg_replace ('/[^0-9\.\-]/i', '', $out [0]) == '')
		throw new Exception ("Impossible to get last version of Titan's CORE! Please, verify if Git is installed and the health of CORE's workcopy.");

	$tag = trim ($out [0]);

	unset ($out);

	exec (GIT .' fetch --all');

	exec (GIT .' describe --abbrev=0 --tags origin/master', $out);

	if (is_array ($out) && array_key_exists (0, $out))
	{
		$last = trim ($out [0]);

		if ($last == $tag)
			echo "INFO > Titan Framework is already updated (version ". preg_replace ('/[^0-9\.\-]/i', '', $last) .")! \n";
		else
		{
			exec (GIT .' stash');

			exec (GIT .' checkout origin/master');

			exec (GIT .' pull origin master');

			exec (GIT .' checkout '. $last);

			exec (GIT .' stash apply');

			setPermission ($_path, octdec ('0775'), octdec ('0664'), 'root', 'staff');

			echo "SUCCESS > Titan Framework [". $_path ."] updated to version ". preg_replace ('/[^0-9\.\-]/i', '', $last) ."! \n";
		}

		$well = preg_replace ('/[^0-9\.\-]/i', '', $last);

		$aux = explode ('-', $well);

		if (sizeof ($aux) == 2)
		{
			$version = $aux [0];
			$release = $aux [1];

			file_put_contents ($_path . DIRECTORY_SEPARATOR .'update'. DIRECTORY_SEPARATOR .'VERSION', $version);
			file_put_contents ($_path . DIRECTORY_SEPARATOR .'update'. DIRECTORY_SEPARATOR .'STABLE', $release);
		}
	}
}
