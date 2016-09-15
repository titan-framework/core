<?php
class Twitter extends Phrase
{
	private $login = '';
	
	private $token = '';
	
	private $secret = '';
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setLoadable (FALSE);
		
		$this->setSavable (FALSE);
		
		if (array_key_exists ('login', $field))
			$this->setLogin ($field ['login']);
		
		if (array_key_exists ('token', $field))
			$this->setToken ($field ['token']);
		
		if (array_key_exists ('secret', $field))
			$this->setSecret ($field ['secret']);
	}
	
	public function save ($id = 0)
	{
		if ($this->isEmpty ())
			return ($this->isRequired () ? FALSE : TRUE);
		
		$message = Message::singleton ();
		
		try
		{
			if (trim ($this->token) == '' || trim ($this->login) == '' || trim ($this->secret) == '')
				throw new Exception (__ ('Twitter post error! Empty username or password.') . (Instance::singleton ()->onDebugMode () && User::singleton ()->isLogged () && User::singleton ()->isAdmin () ? ' ('. __ ('Original Text: [[1]] / Encrypted: [[2]]', $this->getValue (), encrypt ($this->getValue ())) .')' : ''));
			
			$auth = new Zend_Oauth_Token_Access;
			
			$auth->setParams (array ('oauth_token' => $this->token, 
									 'oauth_token_secret' => preg_replace ('/[^0-9a-zA-Z]/i', '', decrypt ($this->secret))));
			
			toLog ('Decrypt: ['. preg_replace ('/[^0-9a-zA-Z]/i', '', decrypt ($this->secret)) .'] ['. $this->token .']');
			
			$twitter = new Zend_Service_Twitter (array ('username' => $this->login, 'accessToken' => $auth));
			
			$response = $twitter->account->verifyCredentials ();
			
			//if (isset ($response->error))
			//	throw new Exception (__ ('Twitter authentication error! [[1]]', $response->error));
			
			$response = $twitter->status->update ($this->getValue ());
			
			$twitter->account->endSession ();
			
			if (isset ($response->error))
				throw new Exception (__ ('Twitter post error! [[1]]', $response->error));
			
			$message->addMessage(__ ('Twitter status updated!'));
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
	}
	
	public function setLogin ($login)
	{
		$this->login = (string) $login;
	}
	
	public function setToken ($token)
	{
		$this->token = (string) $token;
	}
	
	public function setSecret ($secret)
	{
		$this->secret = (string) $secret;
	}
	
	private static function updateStatus ($user, $passwd, $status)
	{
		if (!function_exists ('curl_init')) 
			throw new Exception ('Twitter::updateStatus () needs CURL module. Please, install CURL on your PHP.');
		
		$ch = curl_init();
		
		// Get login form and parse it.
		curl_setopt ($ch, CURLOPT_URL, 'https://mobile.twitter.com/session/new');
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, 'trash.txt');
		curl_setopt ($ch, CURLOPT_COOKIEFILE, 'trash.txt');
		curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3');
		
		$page = curl_exec ($ch);
		$page = stristr ($page, "<div class='signup-body'>");
		
		preg_match ("/form action=\"(.*?)\"/", $page, $action);
		preg_match ("/input name=\"authenticity_token\" type=\"hidden\" value=\"(.*?)\"/", $page, $authenticity_token);
		
		// Make login and get home page.
		$strpost = 'authenticity_token='. urlencode ($authenticity_token [1]) .'&username='. urlencode ($user) .'&password='. urlencode ($passwd);
		curl_setopt ($ch, CURLOPT_URL, $action [1]);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $strpost);
		$page = curl_exec ($ch);
		
		// Check if login was OK.
		preg_match ("/\<div class=\"warning\"\>(.*?)\<\/div\>/", $page, $warning);
		if (isset ($warning [1])) return $warning [1];
		$page = stristr ($page, "<div class='tweetbox'>");
		preg_match ("/form action=\"(.*?)\"/", $page, $action);
		preg_match ("/input name=\"authenticity_token\" type=\"hidden\" value=\"(.*?)\"/", $page, $authenticity_token);
		
		// Send status update.
		$strpost = 'authenticity_token='. urlencode ($authenticity_token [1]);
		$tweet ['display_coordinates'] = '';
		$tweet ['in_reply_to_status_id'] = '';
		$tweet ['lat'] = '';
		$tweet ['long'] = '';
		$tweet ['place_id'] = '';
		$tweet ['text'] = $status;
		$ar = array ('authenticity_token' => $authenticity_token [1], 'tweet' => $tweet);
		$data = http_build_query ($ar);
		curl_setopt ($ch, CURLOPT_URL, $action [1]);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
		$page = curl_exec ($ch);
		
		return TRUE;
	}
}
?>
