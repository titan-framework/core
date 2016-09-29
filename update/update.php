<?php
/**
 * Script for auto update Titan Framework instances.
 *
 * Copyright 2013-2016: PLEASE Lab / Embrapa Gado de Corte
 *
 * @author Camilo Carromeu <camilo.carromeu@embrapa.br>
 * @author Jairo Ricardes Rodrigues Filho <jairocgr@gmail.com>
 * @version 2.0
 *
 * Main script to update proccess.
 */

error_reporting (E_ALL);
set_time_limit (0);
ini_set ('memory_limit', '-1');
ini_set ('register_argc_argv', '1');

require 'binary.php';
require 'function.php';
require 'core.php';
require 'svn.php';
require 'git.php';

$_corePath = dirname (dirname (__FILE__));

$_userPath = getcwd ();

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

	if (!function_exists ('system') || !function_exists ('exec'))
		throw new Exception ("CRITICAL > You need enable OS call functions (verify if PHP is not in safe mode)!");

	$commands = array ('SVN', 'GZIP', 'MV', 'SU', 'GIT');

	foreach ($commands as $trash => $command)
		if (!defined ($command))
			throw new Exception ("CRITICAL > Configure path for binaries of OS in [". $_corePath . DIRECTORY_SEPARATOR ."update". DIRECTORY_SEPARATOR ."binary.php]! \n");

	echo "INFO > Starting auto-update proccess at ". date ('d-m-Y H:i:s') ."... \n";

	$titanUpdateLog = array ();

	echo "INFO > Updating Titan Framework... \n";

	try
	{
		if (isGit ($_corePath))
			updateCoreByGit ($_corePath);
		elseif (isSvn ($_corePath))
			updateCoreBySvn ($_corePath);
		else
			throw new Exception ('Impossible to detect the version control system of Titan\'s CORE. Verify if path ['. $_corePath .'] is a GIT or SVN work copy.');
	}
	catch (Exception $e)
	{
		echo "ERROR > ". $e->getMessage () ." at line ". $e->getLine () ."! \n";
	}

	for ($i = 1; $i < $argc; $i++)
	{
		chdir ($_userPath);

		if (!file_exists ($argv [$i]) || !is_dir ($argv [$i]))
			continue;

		$path = realpath ($argv [$i]);
		
		if (isGit ($path))
			updateInstanceByGit ($path);
		elseif (isSvn ($path))
			updateInstanceBySvn ($path);
		else
			throw new Exception ('Impossible to detect the version control system of instance. Verify if path ['. $_path .'] is a GIT or SVN work copy.');
	}
}
catch (Exception $e)
{
	echo $e->getMessage () ." \n";
}
