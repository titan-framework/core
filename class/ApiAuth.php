<?php
/**
 * Implements authentication in REST-Like API bus.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage api
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Api, ApiEntity, ApiException, ApiList
 * @link http://www.titanframework.com/docs/api/
 */
abstract class ApiAuth
{
	protected $name;

	protected $timeout = 36000;

	protected $token;

	protected $gcmApiKey = NULL;

	protected $sendAlerts = FALSE;

	protected $context = array ();

	protected static $headers = NULL;

	protected $user = NULL;

	protected $register = NULL;

	protected $endpoints = array ();

	const C_USER_LOGIN = 'USER';
	const C_USER_ID = 'USER-BY-ID';
	const C_USER_MAIL = 'USER-BY-MAIL';
	const C_USER_BROWSER = 'USER-BROWSER';
	const C_CLIENT = 'CLIENT';
	const C_CLIENT_USER = 'CLIENT-AS-USER';
	const C_APP = 'APP';

	public function __construct ($app)
	{
		if (!array_key_exists ('name', $app))
			return;

		$this->name = trim ($app ['name']);

		if (array_key_exists ('auth', $app))
		{
			$this->context = explode ('|', $app ['auth']);

			array_walk ($this->context, 'cleanArray');
		}

		if (array_key_exists ('token', $app))
			$this->token = trim ($app ['token']);

		if (array_key_exists ('request-timeout', $app))
			$this->timeout = (int) preg_replace ('/[^0-9]/i', '', $app ['request-timeout']);

		if (array_key_exists ('gcm-api-key', $app))
			$this->gcmApiKey = trim ($app ['gcm-api-key']);

		if (array_key_exists ('send-alerts', $app))
			$this->sendAlerts = strtoupper (trim ($app ['send-alerts'])) == 'TRUE' ? TRUE : FALSE;

		if (array_key_exists ('register-as', $app) && trim ($app ['register-as']) != '' && Security::singleton ()->userTypeExists ($app ['register-as']))
			$this->register = trim ($app ['register-as']);

		if (array_key_exists ('endpoint', $app) && is_array ($app ['endpoint']))
			foreach ($app ['endpoint'] as $trash => $endpoint)
			{
				if (!array_key_exists ($endpoint ['method'], $this->endpoints))
					$this->endpoints [$endpoint ['method']] = array ();

				$this->endpoints [$endpoint ['method']][] = trim (trim ($endpoint ['uri']), '\/');
			}
	}

	public function getUser ()
	{
		return $this->user;
	}

	public function setUser ($id)
	{
		$this->user = (int) $id;

		User::singleton ()->loadById ($id);
	}

	public function getName ()
	{
		return $this->name;
	}

	public function hasContext ()
	{
		$args = func_get_args ();

		foreach ($args as $trash => $auth)
			if (in_array ($auth, $this->context))
				return TRUE;

		return FALSE;
	}

	public function sendAlerts ()
	{
		return $this->sendAlerts;
	}

	public function getRegisterType ()
	{
		if (is_null ($this->register))
			return NULL;

		return Security::singleton ()->getUserType ($this->register);
	}

	public function encrypt ($input)
	{
		return Api::encrypt ($input, md5 ($this->token . (string) $this->timestamp));
	}

	public function decrypt ($input)
	{
		return Api::decrypt ($input, md5 ($this->token . (string) $this->timestamp));
	}

	public function load ()
	{
		$this->loadParamsByHeaders ();
	}

	public function isAccessibleEndpoint ($uri)
	{
		if (!sizeof ($this->endpoints))
			return TRUE;

		foreach ($this->endpoints [Api::getHttpRequestMethod ()] as $trash => $value)
		{
			preg_match ('/'. $value .'/', $uri, $matches);

			// For debug:
			// throw new Exception (print_r ($matches, TRUE) .' # '. strlen ($matches [0]) .' # '. $uri .' # '. strlen (trim ($uri, '/')));

			if (sizeof ($matches) != 1 || strlen ($matches [0]) != strlen (trim ($uri, '/')))
				continue;

			return TRUE;
		}

		return FALSE;
	}

	abstract public function authenticate ();

	abstract protected function loadParamsByHeaders ();

	abstract protected function requiredParamsIsFilled ();

	abstract public function isActive ();

	abstract static protected function signature ($timestamp, $id, $signature);

	abstract static protected function sanitizeParam ($param, $value);

	abstract public static function getHeaders ();

	abstract public function registerGoogleCloudMessage ($gcmRegistrationId);

	abstract public function sendNotification ($user, $message);
}
