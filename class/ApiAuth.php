<?
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
		$this->user = $id;

		User::singleton ()->loadById ($id);
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
			// throw new Exception (print_r ($matches, TRUE));

			if (sizeof ($matches) != 1 || strlen ($matches [0]) != strlen ($uri))
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

/**
 * This class contains implementation for Embrapa-Auth protocol.
 *
 * @author Jairo Rodrigues Filho <jairocgr@gmail.com>
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @todo Move to repos/auth/Embrapa.php
 * @link http://cloud.cnpgc.embrapa.br/embrapa-auth
 */
class EmbrapaAuth extends ApiAuth
{
	const TIMESTAMP = 'x-embrapa-auth-timestamp';

	const USER_ID = 'x-embrapa-auth-user-id';
	const USER_SIGNATURE = 'x-embrapa-auth-user-signature';

	const CLIENT_ID = 'x-embrapa-auth-client-id';
	const CLIENT_SIGNATURE = 'x-embrapa-auth-client-signature';

	const APP_ID = 'x-embrapa-auth-application-id';
	const APP_SIGNATURE = 'x-embrapa-auth-application-signature';

	protected $timestamp = 0;

	protected $userId = '';
	protected $userSignature = '';

	protected $clientId = '';
	protected $clientSignature = '';

	protected $appId = '';
	protected $appSignature = '';

	public function __construct ($app)
	{
		parent::__construct ($app);
	}

	public function authenticateForRegister ()
	{
		if (!$this->timestamp)
			throw new ApiException (__ ('Has a problem with your device clock! Please, verify.'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: UNIX timestamp is empty!');

		if (sizeof (array_intersect (array (self::C_APP), $this->context)) && ($this->name == '' || $this->token == ''))
			throw new ApiException (__ ('Application credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: Application credentials are incorrect or empty!');

		if (time () < $this->timestamp - 180)
			throw new ApiException (__ ('The time of your device must be correct!'), ApiException::ERROR_REQUEST_TIMESTAMP, ApiException::BAD_REQUEST, 'UNIX timestamp of request is invalid (higher than server time)!');

		if (time () - $this->timestamp > $this->timeout)
			throw new ApiException (__ ('Request timeout!'), ApiException::ERROR_REQUEST_TIMESTAMP, ApiException::REQUEST_TIME_OUT, 'UNIX timestamp of request is very old!');

		if (sizeof (array_intersect (array (self::C_APP), $this->context)) && $this->appSignature != self::signature ($this->timestamp, $this->name, $this->token))
			throw new ApiException (__ ('Invalid application credentials!'), ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED);
	}

	public function authenticate ()
	{
		$this->requiredParamsIsFilled ();

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

			MobileDevice::registerDeviceAccess ($this->clientId);

			if (in_array (self::C_CLIENT_USER, $this->context))
				$this->setUser ($client->user);
		}

		if (sizeof (array_intersect (array (self::C_USER_ID, self::C_USER_LOGIN, self::C_USER_MAIL, self::C_USER_BROWSER), $this->context)))
		{
			$db = Database::singleton ();

			$user = NULL;

			if (in_array (self::C_USER_LOGIN, $this->context))
			{
				$sth = $db->prepare ("SELECT _id, _login AS id, _password AS passwd FROM _user WHERE _login = :login LIMIT 1");

				$sth->bindParam (':login', $this->userId, PDO::PARAM_STR);

				$sth->execute ();

				$user = $sth->fetch (PDO::FETCH_OBJ);
			}
			elseif (in_array (self::C_USER_ID, $this->context))
			{
				$sth = $db->prepare ("SELECT _id, _id AS id, _password AS passwd FROM _user WHERE _id = :id LIMIT 1");

				$uid = (int) preg_replace ('/[^0-9]/i', '', $this->userId);

				$sth->bindParam (':id', $uid, PDO::PARAM_INT);

				$sth->execute ();

				$user = $sth->fetch (PDO::FETCH_OBJ);
			}
			elseif (in_array (self::C_USER_MAIL, $this->context) || in_array (self::C_USER_BROWSER, $this->context))
			{
				if (!Database::isUnique ('_user', '_email'))
					throw new ApiException (__ ('e-Mail must be unique to authenticate user! Please, report to system administrator.'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

				$sth = $db->prepare ("SELECT _id, _email AS id, _password AS passwd FROM _user WHERE _email = :mail LIMIT 1");

				$sth->bindParam (':mail', $this->userId, PDO::PARAM_STR);

				$sth->execute ();

				$user = $sth->fetch (PDO::FETCH_OBJ);
			}

			if (!is_object ($user))
				throw new ApiException (__ ('User not found!'), ApiException::ERROR_USER_AUTH, ApiException::UNAUTHORIZED);

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

		return TRUE;
	}

	protected function loadParamsByHeaders ()
	{
		$headers = self::getHeaders ();

		$params = array (self::TIMESTAMP => 'timestamp',
						 self::USER_ID => 'userId',
						 self::USER_SIGNATURE => 'userSignature',
						 self::CLIENT_ID => 'clientId',
						 self::CLIENT_SIGNATURE => 'clientSignature',
						 self::APP_ID => 'appId',
						 self::APP_SIGNATURE => 'appSignature');

		foreach ($params as $key => $param)
			$this->$param = array_key_exists ($key, $headers) ? self::sanitizeParam ($key, $headers [$key]) : NULL;
	}

	protected function requiredParamsIsFilled ()
	{
		if (!$this->timestamp)
			throw new ApiException (__ ('Has a problem with your device clock! Please, verify.'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: UNIX timestamp is empty!');

		if (sizeof (array_intersect (array (self::C_USER_ID, self::C_USER_LOGIN, self::C_USER_MAIL), $this->context)) && ($this->userId == '' || $this->userSignature == ''))
			throw new ApiException (__ ('User credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: User credentials are incorrect or empty!');

		if (sizeof (array_intersect (array (self::C_CLIENT, self::C_CLIENT_USER), $this->context)) && ($this->clientId == '' || $this->clientSignature == ''))
			throw new ApiException (__ ('Client credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: Client credentials are incorrect or empty!');

		if (sizeof (array_intersect (array (self::C_APP), $this->context)) && ($this->name == '' || $this->token == ''))
			throw new ApiException (__ ('Application credentials are incorrect or empty!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST, 'Invalid header parameter: Application credentials are incorrect or empty!');
	}

	public static function getHeaders ()
	{
		if (is_null (self::$headers))
			self::$headers = apache_request_headers ();

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
			case self::TIMESTAMP:

				return (int) preg_replace ('/[^0-9]/i', '', $value);

			case self::APP_SIGNATURE:
			case self::CLIENT_SIGNATURE:
			case self::USER_SIGNATURE:

				$value = preg_replace ('/[^0-9A-Fa-f]/i', '', $value);

				if (strlen ($value) != 40)
					return NULL;

				return $value;
		}

		return $value;
	}

	public function isActive ()
	{
		$headers = self::getHeaders ();

		if (!is_array ($headers) || !array_key_exists (self::APP_ID, $headers) || trim ($headers [self::APP_ID]) != $this->name)
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
}
?>
