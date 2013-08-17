<?
abstract class ApiAuth
{
	protected $name;
	
	protected $timeout = 36000;
	
	protected $token;
	
	protected $context = array ();
	
	protected static $headers = NULL;
	
	protected $user = NULL;
	
	const C_USER_LOGIN = 'USER';
	const C_USER_ID = 'USER-BY-ID';
	const C_USER_MAIL = 'USER-BY-MAIL';
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
	}
	
	public function getUser ()
	{
		return $this->user;
	}
	
	abstract public function authenticate ();
	
	abstract protected function loadParamsByHeaders ();
	
	abstract protected function requiredParamsIsFilled ();
	
	abstract public function isActive ();
	
	abstract static protected function encrypt ($timestamp, $id, $signature);
	
	abstract static protected function sanitizeParam ($param, $value);
	
	abstract public static function getHeaders ();
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
	
	public function authenticate ()
	{
		$this->loadParamsByHeaders ();
		
		$this->requiredParamsIsFilled ();
		
		if (time () < $this->timestamp)
			throw new ApiException ('UNIX timestamp of request is invalid (higher than server time)!', ApiException::BAD_REQUEST);
		
		if (time () - $this->timestamp > $this->timeout)
			throw new ApiException ('UNIX timestamp of request is very old!', ApiException::REQUEST_TIME_OUT);
		
		if (sizeof (array_intersect (array (self::C_APP), $this->context)) && $this->appSignature != self::encrypt ($this->timestamp, $this->name, $this->token))
			throw new ApiException ('Invalid application credentials!', ApiException::UNAUTHORIZED);
		
		if (sizeof (array_intersect (array (self::C_CLIENT, self::C_CLIENT_USER), $this->context)))
		{
			if (!MobileDevice::isActive ())
				throw new ApiException ('Register of credentials for mobile devices is not enabled!', ApiException::UNAUTHORIZED);
			
			$client = MobileDevice::getRegisteredDevice ($this->clientId);
			
			if (!is_object ($client))
				throw new ApiException ('This client is not registered!', ApiException::UNAUTHORIZED);
			
			if ($this->clientSignature != self::encrypt ($this->timestamp, $client->id, $client->pk))
				throw new ApiException ('Invalid client credentials!', ApiException::UNAUTHORIZED);
			
			if (in_array (self::C_CLIENT_USER, $this->context))
				$this->user = $client->user;
		}
		
		if (sizeof (array_intersect (array (self::C_USER_ID, self::C_USER_LOGIN, self::C_USER_MAIL), $this->context)))
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
				
				$sth->bindParam (':id', (int) preg_replace ('/[^0-9]/i', '', $this->userId), PDO::PARAM_INT);
				
				$sth->execute ();
				
				$user = $sth->fetch (PDO::FETCH_OBJ);
			}
			elseif (in_array (self::C_USER_MAIL, $this->context))
			{
				if (!Database::isUnique ('_user', '_email'))
					throw new ApiException ('e-Mail column must be unique to authenticate user! Please, report to system administrator.', ApiException::UNAUTHORIZED);
				
				$sth = $db->prepare ("SELECT _id, _email AS id, _password AS passwd FROM _user WHERE _email = :mail LIMIT 1");
				
				$sth->bindParam (':mail', $this->userId, PDO::PARAM_STR);
				
				$sth->execute ();
				
				$user = $sth->fetch (PDO::FETCH_OBJ);
			}
			
			if (!is_object ($user))
				throw new ApiException ('User not found!', ApiException::UNAUTHORIZED);
			
			if ($this->userSignature != self::encrypt ($this->timestamp, $user->id, $user->passwd))
				throw new ApiException ('Invalid user credentials!', ApiException::UNAUTHORIZED);
			
			$this->user = $user->_id;
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
			throw new ApiException ('Invalid header parameter: UNIX timestamp is empty!', ApiException::BAD_REQUEST);
		
		if (sizeof (array_intersect (array (self::C_USER_ID, self::C_USER_LOGIN, self::C_USER_MAIL), $this->context)) && ($this->userId == '' || $this->userSignature == ''))
			throw new ApiException ('Invalid header parameter: User credentials are incorrect or empty!', ApiException::BAD_REQUEST);
		
		if (sizeof (array_intersect (array (self::C_CLIENT, self::C_CLIENT_USER), $this->context)) && ($this->clientId == '' || $this->clientSignature == ''))
			throw new ApiException ('Invalid header parameter: Client credentials are incorrect or empty!', ApiException::BAD_REQUEST);
		
		if (sizeof (array_intersect (array (self::C_APP), $this->context)) && ($this->clientId == '' || $this->clientSignature == ''))
			throw new ApiException ('Invalid header parameter: Application credentials are incorrect or empty!', ApiException::BAD_REQUEST);
	}
	
	public static function getHeaders ()
	{
		if (is_null (self::$headers))
			self::$headers = apache_request_headers ();
		
		return self::$headers;
	}
	
	static protected function encrypt ($timestamp, $id, $signature)
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
}
?>