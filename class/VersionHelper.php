<?php
/**
 * Help to get version and build numbers of Titan.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage util
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance
 */
class VersionHelper
{
	static private $helper = FALSE;

	private $titanVersion = '';
	private $titanBuild = '';

	private $usingAutoDeploy = FALSE;

	private $appVersion = '';
	private $appBuild = '';
	private $appEnvironment = '';
	private $appDate = '';
	private $appAuthor = '';

	private final function __construct ()
	{
		$coreUpdatePath = Instance::singleton ()->getCorePath () .'update'. DIRECTORY_SEPARATOR;

		$this->titanVersion = trim (file_get_contents ($coreUpdatePath .'VERSION'));
		$this->titanBuild = trim (file_get_contents ($coreUpdatePath .'STABLE'));

		$appVersionPath = 'update'. DIRECTORY_SEPARATOR .'VERSION';

		if (file_exists ($appVersionPath) && is_readable ($appVersionPath))
			$this->appVersion = trim (file_get_contents ($appVersionPath, 0, NULL, 0, 16));

		$appReleasePath = Instance::singleton ()->getCachePath () .'RELEASE';

		if (file_exists ($appReleasePath) && is_readable ($appReleasePath))
		{
			$file = parse_ini_file ($appReleasePath);

			if (is_array ($file))
			{
				$autoDeploy = TRUE;

				$requiredKeys = array ('version', 'environment', 'date', 'author');

				foreach ($requiredKeys as $trash => $key)
					if (!array_key_exists ($key, $file) || trim ((string) $file [$key]) == '')
						$autoDeploy = FALSE;

				if ($autoDeploy)
				{
					$this->usingAutoDeploy = TRUE;

					$this->appBuild = trim ($file ['version']);
					$this->appEnvironment = trim ($file ['environment']);
					$this->appDate = strftime ('%x %X', trim ($file ['date']));
					$this->appAuthor = trim ($file ['author']);
				}
			}
		}
	}

	static public function singleton ()
	{
		if (self::$helper !== FALSE)
			return self::$helper;

		$class = __CLASS__;

		self::$helper = new $class ();

		return self::$helper;
	}

	public function getTitanVersion ()
	{
		return $this->titanVersion;
	}

	public function getTitanBuild ()
	{
		return $this->titanBuild;
	}

	public function getTitanRelease ()
	{
		return $this->getTitanVersion () .'-'. $this->getTitanBuild ();
	}

	public function usingAutoDeploy ()
	{
		return $this->usingAutoDeploy;
	}

	public function getAppVersion ()
	{
		return $this->appVersion;
	}

	public function getAppBuild ()
	{
		return $this->appBuild;
	}

	public function getAppRelease ()
	{
		return $this->getAppVersion () .'-'. $this->getAppBuild ();
	}

	public function getAppEnvironment ()
	{
		return $this->appEnvironment;
	}

	public function getAppDate ()
	{
		return $this->appDate;
	}

	public function getAppAuthor ()
	{
		return $this->appAuthor;
	}
}
