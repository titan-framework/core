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
		if ($this->name == '' || $this->token == '')
			throw new ApiException (__ ('Application credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: Application credentials are incorrect or empty!');
	}

	public function authenticate ()
	{
		$this->requiredParamsIsFilled ();

		/*
		$db = Database::singleton ();

		if (time () < $this->timestamp - 180)
			throw new ApiException (__ ('The time of your device must be correct!'), ApiException::ERROR_REQUEST_TIMESTAMP, ApiException::BAD_REQUEST, 'UNIX timestamp of request is invalid (higher than server time)!');

		if (time () - $this->timestamp > $this->timeout)
			throw new ApiException (__ ('Request timeout!'), ApiException::ERROR_REQUEST_TIMESTAMP, ApiException::REQUEST_TIME_OUT, 'UNIX timestamp of request is very old!');

		if (sizeof (array_intersect (array (self::C_APP), $this->context)) && $this->appSignature != self::signature ($this->timestamp, $this->name, $this->token))
			throw new ApiException (__ ('Invalid application credentials!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);

		if (sizeof (array_intersect (array (self::C_CLIENT, self::C_CLIENT_USER), $this->context)))
		{
			if (!MobileDevice::isActive ())
				throw new ApiException (__ ('Register of credentials for mobile devices is not enabled!'), ApiException::ERROR_CLIENT_AUTH, ApiException::UNAUTHORIZED);

			$client = MobileDevice::getRegisteredDevice ($this->clientId);

			if (!is_object ($client))
				throw new ApiException (__ ('This client is not registered!'), ApiException::ERROR_CLIENT_AUTH, ApiException::UNAUTHORIZED);

			if ($this->clientSignature != self::signature ($this->timestamp, $client->id, $client->pk))
				throw new ApiException (__ ('Invalid client credentials!'), ApiException::ERROR_CLIENT_AUTH, ApiException::UNAUTHORIZED);

			if (in_array (self::C_CLIENT_USER, $this->context))
			{
				$sth = $db->prepare ("SELECT _id FROM _user WHERE _id = :id AND _active = B'1' AND _deleted = B'0' LIMIT 1");

				$sth->bindParam (':id', $client->user, PDO::PARAM_INT);

				$sth->execute ();

				if (!is_object ($sth->fetch (PDO::FETCH_OBJ)))
					throw new ApiException (__ ('User does not exist or is inactive!'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

				$this->setUser ($client->user);
			}

			MobileDevice::registerDeviceAccess ($this->clientId);
		}

		if (sizeof (array_intersect (array (self::C_USER_ID, self::C_USER_LOGIN, self::C_USER_MAIL, self::C_USER_BROWSER), $this->context)))
		{
			$user = NULL;

			if (in_array (self::C_USER_LOGIN, $this->context))
			{
				$sth = $db->prepare ("SELECT _id, _login AS id, _password AS passwd FROM _user WHERE _login = :login AND _active = B'1' AND _deleted = B'0' LIMIT 1");

				$sth->bindParam (':login', $this->userId, PDO::PARAM_STR);

				$sth->execute ();

				$user = $sth->fetch (PDO::FETCH_OBJ);
			}
			elseif (in_array (self::C_USER_ID, $this->context))
			{
				$sth = $db->prepare ("SELECT _id, _id AS id, _password AS passwd FROM _user WHERE _id = :id AND _active = B'1' AND _deleted = B'0' LIMIT 1");

				$uid = (int) preg_replace ('/[^0-9]/i', '', $this->userId);

				$sth->bindParam (':id', $uid, PDO::PARAM_INT);

				$sth->execute ();

				$user = $sth->fetch (PDO::FETCH_OBJ);
			}
			elseif (in_array (self::C_USER_MAIL, $this->context) || in_array (self::C_USER_BROWSER, $this->context))
			{
				if (!Database::isUnique ('_user', '_email'))
					throw new ApiException (__ ('e-Mail must be unique to authenticate user! Please, report to system administrator.'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

				$sth = $db->prepare ("SELECT _id, _email AS id, _password AS passwd FROM _user WHERE _email = :mail AND _active = B'1' AND _deleted = B'0' LIMIT 1");

				$sth->bindParam (':mail', $this->userId, PDO::PARAM_STR);

				$sth->execute ();

				$user = $sth->fetch (PDO::FETCH_OBJ);
			}

			if (!is_object ($user))
				throw new ApiException (__ ('User does not exist or is inactive!'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

			if (in_array (self::C_USER_BROWSER, $this->context))
			{
				$pk = BrowserDevice::getKeyForRegisteredUser ($user->_id);

				if ($this->userSignature != self::signature ($this->timestamp, $user->id, $pk))
					throw new ApiException (__ ('Invalid user credentials!'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

				BrowserDevice::registerAccess ($user->_id);
			}
			elseif ($this->userSignature != self::signature ($this->timestamp, $user->id, $user->passwd))
				throw new ApiException (__ ('Invalid user credentials!'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

			$this->setUser ($user->_id);
		}
		*/

		return TRUE;
	}

	protected function loadParamsByHeaders ()
	{
		$headers = self::getHeaders ();

		$params = array (self::APP => 'app',
						 self::SIGNATURE => 'signature');

		foreach ($params as $key => $param)
			$this->$param = array_key_exists ($key, $headers) ? self::sanitizeParam ($key, $headers [$key]) : NULL;
	}

	protected function requiredParamsIsFilled ()
	{
		/*
		if (!$this->timestamp)
			throw new ApiException (__ ('Has a problem with your device clock! Please, verify.'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: UNIX timestamp is empty!');

		if (sizeof (array_intersect (array (self::C_USER_ID, self::C_USER_LOGIN, self::C_USER_MAIL), $this->context)) && ($this->userId == '' || $this->userSignature == ''))
			throw new ApiException (__ ('User credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: User credentials are incorrect or empty!');

		if (sizeof (array_intersect (array (self::C_CLIENT, self::C_CLIENT_USER), $this->context)) && ($this->clientId == '' || $this->clientSignature == ''))
			throw new ApiException (__ ('Client credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: Client credentials are incorrect or empty!');

		if (sizeof (array_intersect (array (self::C_APP), $this->context)) && ($this->name == '' || $this->token == ''))
			throw new ApiException (__ ('Application credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: Application credentials are incorrect or empty!');
		*/
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

	static protected function signature ($timestamp, $id, $signature)
	{
		return hash_hmac ('sha1', $timestamp . $id, $signature);
	}

	static protected function sanitizeParam ($param, $value)
	{
		$value = trim ($value);

		switch ($param)
		{
			case self::SIGNATURE:

				if (!preg_match ('/^Bearer:\s*([\w-]+\.[\w-]+\.[\w-]+)$/', $value, $match) || sizeof ($match) < 2 || trim ($match [1]) == '')
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
