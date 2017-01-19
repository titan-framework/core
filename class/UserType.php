<?php
/**
 * Load user type definitions.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage security
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Security, User, Group, AjaxLogon, AjaxPasswd, Ldap
 */
class UserType
{
	private $label = '';

	private $name = '';

	private $description = '';

	private $type = '';

	private $ldap = '';

	private $showRegister = FALSE;

	private $formRegister = 'register.xml';

	private $formModify = 'modify.xml';

	private $formLdap = 'ldap.xml';

	private $profile = NULL;

	private $home = '';

	const TPRIVATE = '_TYPE_PRIVATE_';
	const TPROTECTED = '_TYPE_PROTECTED_';
	const TPUBLIC = '_TYPE_PUBLIC_';

	public function __construct ($input)
	{
		if (!is_array ($input))
			throw new Exception ('Entrada para mapeamento do tipo de usuário não é um vetor!');

		if (array_key_exists ('label', $input))
			$this->setLabel ($input ['label']);

		if (array_key_exists ('name', $input))
			$this->setName ($input ['name']);

		if (array_key_exists ('type', $input))
			$this->setType ($input ['type']);

		if (array_key_exists ('form-register', $input))
			$this->setRegister ($input ['form-register']);

		if (array_key_exists ('form-modify', $input))
			$this->setModify ($input ['form-modify']);

		if (array_key_exists ('form-ldap', $input))
			$this->setLdapForm ($input ['form-ldap']);

		if (array_key_exists ('description', $input))
			$this->setDescription ($input ['description']);

		if (array_key_exists ('ldap', $input))
			$this->setLdap ($input ['ldap']);

		if (array_key_exists ('profile', $input))
			$this->setProfile ($input ['profile']);

		if (array_key_exists ('register-on-logon', $input))
			$this->showRegister = strtoupper (trim ($input ['register-on-logon'])) == 'TRUE' ? TRUE : FALSE;

		if (array_key_exists ('home', $input))
			$this->home = trim ($input ['home']);
	}

	public function setLabel ($label)
	{
		$array = explode ('|', $label);

		if (sizeof ($array) > 1)
		{
			$language = Localization::singleton ()->getLanguage ();

			foreach ($array as $key => $value)
			{
				$aux = explode (':', $value);

				if (!$key)
					$label = sizeof ($aux) > 1 ? $aux [1] : $aux [0];

				if ($language != trim ($aux [0]))
					continue;

				$label = trim ($aux [1]);

				break;
			}
		}

		$this->label = $label;
	}

	public function getLabel ()
	{
		return $this->label;
	}

	public function setName ($name)
	{
		$this->name = $name;
	}

	public function getName ()
	{
		return $this->name;
	}

	public function setType ($type)
	{
		switch ($type)
		{
			case 'private':
				$this->type = self::TPRIVATE;
				break;

			case 'protected':
				$this->type = self::TPROTECTED;
				break;

			case 'public':
				$this->type = self::TPUBLIC;
				break;
		}
	}

	public function getType ()
	{
		return $this->type;
	}

	public function setRegister ($formRegister)
	{
		$this->formRegister = $formRegister;
	}

	public function getRegister ()
	{
		return $this->formRegister;
	}

	public function setModify ($formModify)
	{
		$this->formModify = $formModify;
	}

	public function getModify ()
	{
		return $this->formModify;
	}

	public function setLdapForm ($formLdap)
	{
		$this->formLdap = $formLdap;
	}

	public function getLdapForm ()
	{
		return $this->formLdap;
	}

	public function setDescription ($description)
	{
		$this->description = $description;
	}

	public function getDescription ()
	{
		return $this->description;
	}

	public function setLdap ($ldap)
	{
		$this->ldap = trim ($ldap);
	}

	public function getLdap ()
	{
		if (!$this->useLdap ())
			return NULL;

		return Security::singleton ()->getLdapHost ($this->ldap);
	}

	public function useLdap ()
	{
		if (trim ($this->ldap) == '')
			return FALSE;

		if (!Security::singleton ()->ldapExists ($this->ldap))
			throw new Exception ('Dont exists a valid LDAP host for this user type! Verify [configure/security.xml] if user type configuration use a valid LDAP host seted on [configure/ldap.xml].');

		return TRUE;
	}

	public function setProfile ($profile)
	{
		$this->profile = trim ($profile);
	}

	public function getProfile ()
	{
		$aux = explode ('/', $this->profile);

		if (!is_array ($aux) || sizeof ($aux) != 2)
			return FALSE;

		$user = User::singleton ();

		if ($section = Business::singleton ()->getSection (trim ($aux [0])))
			if ($action = $section->getAction (trim ($aux [1])))
				if ($user->accessAction ($action->getName (), $section->getName ()))
					return 'titan.php?target=body&toSection='. $section->getName () .'&toAction='. $action->getName ();

		return FALSE;
	}

	public function showRegister ()
	{
		return $this->showRegister;
	}

	public function getHome ()
	{
		return $this->home;
	}
}
