<?php
/**
 * Contract to create classes to LDAP.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage security
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Ldap, Security, User, UserType, Group, AjaxLogon, AjaxPasswd
 */
class LdapClass
{
	protected $path = '';

	protected $ldap = NULL;

	public function __construct (&$ldap, $path, $array)
	{
		$this->path = $path;

		$this->ldap = $ldap;
	}

	public function getPath ()
	{
		return $this->path;
	}

	public function getLdap ()
	{
		return $this->ldap;
	}

	public function genRequiredFields ($uid, $name, $email, $password, $id)
	{
		return array ();
	}

	public function genPasswordFields ($uid, $password)
	{
		return array ();
	}
}
