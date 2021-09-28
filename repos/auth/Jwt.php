<?php

use Firebase\JWT\JWT;

/**
 * This class contains implementation for JWT protocol.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @todo Move to repos/auth/Jwt.php
 * @link https://jwt.io/
 */
class JwtAuth extends ApiAuth
{
	const APP = 'application';
	const SIGNATURE = 'authorization';

	protected $app = '';
	protected $signature = '';

	public function __construct ($app)
	{
		parent::__construct ($app);
	}

	public function authenticateForRegister ()
	{
		$unsupported = [ self::C_USER_LOGIN, self::C_USER_ID, self::C_USER_BROWSER, self::C_CLIENT, self::C_CLIENT_USER ];

		if (sizeof (array_intersect ($unsupported, $this->context)))
			throw new ApiException (__ ('Unsupported context used! Please, report to system administrator.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'The REST-Like API bus when using JWT do not support yet the following contexts: '. implode (', ', $unsupported) .'!');

		if ($this->name == '' || $this->token == '')
			throw new ApiException (__ ('Application credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Impossible to load application by header parameter!');
	
		if (!Database::isUnique ('_user', '_email'))
			throw new ApiException (__ ('e-Mail must be unique to authenticate user! Please, report to system administrator.'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);
	}

	public function authenticate ()
	{
		$unsupported = [ self::C_USER_LOGIN, self::C_USER_ID, self::C_USER_BROWSER, self::C_CLIENT, self::C_CLIENT_USER ];

		if (sizeof (array_intersect ($unsupported, $this->context)))
			throw new ApiException (__ ('Unsupported context used! Please, report to system administrator.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'The REST-Like API bus when using JWT do not support yet the following contexts: '. implode (', ', $unsupported) .'!');
		
		if ($this->name == '' || $this->token == '')
			throw new ApiException (__ ('Application credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Impossible to load application by header parameter!');
		
		if ($this->signature == '')
			throw new ApiException (__ ('Application credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: Application or/and Authorization are incorrect or empty!');

		$db = Database::singleton ();
		
		if (!Database::isUnique ('_user', '_email'))
			throw new ApiException (__ ('e-Mail must be unique to authenticate user! Please, report to system administrator.'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

		$payload = $this->decrypt ($this->signature);

		if (!$payload || !is_object ($payload) || !isset ($payload->email) || trim ($payload->email) == '' || !preg_match('/^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/', $payload->email))
			throw new ApiException (__ ('Invalid bearer!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

		$sth = $db->prepare ("SELECT _id, _email AS id, _password AS passwd FROM _user WHERE _email = :mail AND _active = B'1' AND _deleted = B'0' LIMIT 1");

		$sth->bindParam (':mail', $payload->email, PDO::PARAM_STR);

		$sth->execute ();

		$user = $sth->fetch (PDO::FETCH_OBJ);

		if (!is_object ($user))
			throw new ApiException (__ ('User does not exist or is inactive!'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

		$this->setUser ($user->_id);

		return TRUE;
	}

	protected function loadParamsByHeaders ()
	{
		$headers = self::getHeaders ();

		$params = [
			self::APP => 'app',
			self::SIGNATURE => 'signature'
		];

		foreach ($params as $key => $param)
			$this->$param = array_key_exists ($key, $headers) ? self::sanitizeParam ($key, $headers [$key]) : NULL;
	}

	public static function getHeaders ()
	{
		if (is_null (self::$headers))
        {
			self::$headers = apache_request_headers ();

			foreach (self::$headers as $key => $value)
			    self::$headers [strtolower ($key)] = $value;
        }

		return self::$headers;
	}

	static protected function sanitizeParam ($param, $value)
	{
		$value = trim ($value);

		switch ($param)
		{
			case self::SIGNATURE:

				if (!preg_match ('/^Bearer\s+([\w-]+\.[\w-]+\.[\w-]+)$/', $value, $match) || sizeof ($match) < 2 || trim ($match [1]) == '')
					return NULL;

				return $match [1];
		}

		return $value;
	}

	public function isActive ()
	{
		$headers = self::getHeaders ();

		if (!is_array ($headers) || !array_key_exists (self::APP, $headers) || trim ($headers [self::APP]) != $this->name)
			return FALSE;

		return TRUE;
	}

	public function registerGoogleCloudMessage ($gcmRegistrationId)
	{
		try
		{
			return MobileDevice::registerGoogleCloudMessage ($this->clientId, $gcmRegistrationId);
		}
		catch (Exception $e)
		{
			throw new ApiException ($e->getMessage (), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);
		}
	}

	public function sendNotification ($user, $message)
	{
		if (is_integer ($user))
			$user = array ($user);

		if (!is_array ($user) || !sizeof ($user) || !is_array ($message) || !sizeof ($message))
			return FALSE;

		$sth = Database::singleton ()->prepare ("SELECT _gcm FROM _mobile WHERE _user IN (". implode (",", $user) .") AND _gcm IS NOT NULL");

		$sth->execute ();

		$ids = $sth->fetchAll (PDO::FETCH_COLUMN);

		if (sizeof ($ids))
			MobileDevice::sendNotification ($this->gcmApiKey, $ids, $message);
	}

	public function encrypt ($payload)
	{
		return JWT::encode ($payload, $this->token);
	}

	public function decrypt ($jwt)
	{
		return JWT::decode($jwt, $this->token, array('HS256'));
	}
}
