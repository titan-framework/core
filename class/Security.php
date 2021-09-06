<?php
/**
 * Security definitions class. This class is used for instantiate a singleton
 * object for security definitions.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage security
 * @copyright 2005-2021 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see User, UserType, Group, AjaxLogon, AjaxPasswd, Ldap
 */
class Security
{
	static private $security = FALSE;

	private $array = array ();

	private $userTypes = array ();

	private $ldapHosts = array ();

	private final function __construct ()
	{
		$instance = Instance::singleton ();

		$fromXml = $instance->getSecurity ();

		$this->array = array (	'xml-path'			=> '',
								'timeout'			=> 1800,
								'hash'				=> '',
								'ldap-xml-path'		=> '',
								'encrypt-on-client'	=> TRUE);

		foreach ($this->array as $key => $trash)
			if (array_key_exists ($key, $fromXml) && trim ($fromXml [$key]) != '')
				if (is_bool ($this->array [$key]))
					$this->array [$key] = strtoupper ($fromXml [$key]) == 'TRUE' ? TRUE : FALSE;
				else
					$this->array [$key] = trim ($fromXml [$key]);
		
		if (trim ($this->array ['hash']) == '')
			if (isset ($_ENV ['TITAN_SECURITY_HASH']) && trim ($_ENV ['TITAN_SECURITY_HASH']) != '')
				$this->array ['hash'] = $_ENV ['TITAN_SECURITY_HASH'];
			else
				$this->array ['hash'] = '24434ca29c539e014f9cbb3191831dc6';

		if (array_key_exists ('ldap-xml-path', $fromXml) && file_exists ($fromXml ['ldap-xml-path']))
		{
			$file = $fromXml ['ldap-xml-path'];

			$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';

			if (file_exists ($cacheFile))
				$array = include $cacheFile;
			else
			{
				$xml = new Xml ($file);

				$array = $xml->getArray ();

				$array = $array ['ldap-mapping'][0];

				xmlCache ($cacheFile, $array);
			}

			foreach ($this->array as $key => $trash)
				if (array_key_exists ($key, $array))
					$this->array [$key] = trim ($this->path . $array [$key]);

			if (array_key_exists ('ldap', $array))
			{
				if (!is_array ($array ['ldap']))
					$array ['ldap'] = array ($array ['ldap']);

				foreach ($array ['ldap'] as $key => $ldap)
				{
					if (!array_key_exists ('host', $ldap))
						continue;
					elseif (!array_key_exists ('id', $ldap))
						$ldap ['id'] = $ldap ['host'];

					$this->ldapHosts [$ldap ['id']] = new Ldap ($ldap);
				}
			}
		}

		if (array_key_exists ('xml-path', $fromXml) && file_exists ($fromXml ['xml-path']))
		{
			$file = $fromXml ['xml-path'];

			$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';

			if (file_exists ($cacheFile))
				$array = include $cacheFile;
			else
			{
				$xml = new Xml ($file);

				$array = $xml->getArray ();

				$array = $array ['security-mapping'][0];

				xmlCache ($cacheFile, $array);
			}

			foreach ($this->array as $key => $trash)
				if (array_key_exists ($key, $array))
					$this->array [$key] = trim ($this->path . $array [$key]);

			if (array_key_exists ('user-type', $array))
			{
				if (!is_array ($array ['user-type']))
					$array ['user-type'] = array ($array ['user-type']);

				foreach ($array ['user-type'] as $key => $userType)
				{
					if (!array_key_exists ('name', $userType))
						continue;

					$this->userTypes [$userType ['name']] = new UserType ($userType);
				}
			}
		}
	}

	static public function singleton ()
	{
		if (self::$security !== FALSE)
			return self::$security;

		$class = __CLASS__;

		self::$security = new $class ();

		return self::$security;
	}

	public function getTimeout ()
	{
		return $this->array ['timeout'];
	}

	public function getHash ()
	{
		return $this->array ['hash'];
	}

	public function encryptOnClient ()
	{
		return $this->array ['encrypt-on-client'];
	}

	public function userTypeExists ($name)
	{
		return array_key_exists ($name, $this->userTypes);
	}

	public function getUserType ($name = FALSE)
	{
		if ($name !== FALSE)
		{
			if (array_key_exists ($name, $this->userTypes))
				return $this->userTypes [$name];

			return NULL;
		}

		$userType = each ($this->userTypes);

		if ($userType !== FALSE)
			return $userType ['value'];

		reset ($this->userTypes);

		return NULL;
	}

	public function getLdapHost ($id = FALSE)
	{
		if ($id !== FALSE)
		{
			if (array_key_exists ($id, $this->ldapHosts))
				return $this->ldapHosts [$id];

			return NULL;
		}

		$ldapHost = each ($this->ldapHosts);

		if ($ldapHost !== FALSE)
			return $ldapHost ['value'];

		reset ($this->ldapHosts);

		return NULL;
	}

	public function ldapExists ($host)
	{
		return array_key_exists ($host, $this->ldapHosts);
	}

	public function allowRegister ($name)
	{
		$userType = $this->getUserType ($name);

		if (!$userType)
			return FALSE;

		if ($userType->getType () == UserType::TPRIVATE)
			return FALSE;

		return TRUE;
	}
}
