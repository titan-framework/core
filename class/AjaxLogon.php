<?
class AjaxLogon
{
	public function logon ($formData)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($formData ['login']) || !isset ($formData ['password']) || trim ($formData ['login']) == '' || trim ($formData ['password']) == '')
				throw new Exception (__ ('Fill correct all fields to logon!'));

			$user = User::singleton ();

			$user->authenticate ($formData ['login'], $formData ['password']);
			
			Log::singleton ()->add ('LOGON', '', Log::SECURITY, FALSE, TRUE);

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function lostPassword ($login)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($login) || trim ($login) == '')
				throw new Exception (__ ('Insert your login for recover the password!'));

			$validate = array ("'", '"', '\\', '--', '/*', '*/');
			$validLogin = str_replace ($validate, '', $login);

			if ($login !== $validLogin)
				throw new Exception (__ ('This login does not registered in our database or user was disabled.'));

			$db = Database::singleton ();
			
			if (Database::isUnique ('_user', '_email'))
				$sth = $db->prepare ("SELECT _name, _email, _login, _password, _type FROM _user WHERE (_login = '". $login ."' OR _email = '". $login ."') AND _deleted = '0'");
			else
				$sth = $db->prepare ("SELECT _name, _email, _login, _password, _type FROM _user WHERE _login = '". $login ."' AND _deleted = '0'");

			$sth->execute ();

			$obj = $sth->fetch (PDO::FETCH_OBJ);

			if (!$obj)
				throw new Exception (__ ('This login does not registered in our database or user was disabled.'));

			$name   = $obj->_name;
			$email  = $obj->_email;
			$passwd = $obj->_password;
			$login  = $obj->_login;

			if (Security::singleton ()->getUserType ($obj->_type)->useLdap ())
			{
				$ldap = Security::singleton ()->getUserType ($obj->_type)->getLdap ();

				$fields = array ('userPassword', 'mail', 'cn');

				$ldap->connect (FALSE, FALSE, TRUE);

				$result = $ldap->load ($login, $fields);

				$name   = $result ['cn'];
				$email  = $result ['mail'];
				$passwd = $result ['userpassword'];

				$ldap->close ();
			}

			$mail = Mail::singleton ();

			$subject = $mail->getForgot ('subject');

			$msg = $mail->getForgot ('text');

			$instance = Instance::singleton ();

			$security = Security::singleton ();
			
			if ($instance->getFriendlyUrl ('change-password') == '')
				$link = $instance->getUrl () ."titan.php?target=remakePasswd&login=". urlencode ($login) ."&hash=". shortlyHash (sha1 ($security->getHash () . $name . $security->getHash () . $passwd . $security->getHash () . $email . $security->getHash ()));
			else
				$link = $instance->getUrl () . $instance->getFriendlyUrl ('change-password') ."/". urlencode ($login) ."/". shortlyHash (sha1 ($security->getHash () . $name . $security->getHash () . $passwd . $security->getHash () . $email . $security->getHash ()));
			
			$search  = array ('[USER]', '[NAME]', '[LINK]', '[LOGIN]');
			$replace = array ($name, html_entity_decode ($instance->getName (), ENT_QUOTES, 'UTF-8'), $link, $login);

			$subject = str_replace ($search, $replace, $subject);
			$msg = str_replace ($search, $replace, $msg);

			$headers  = "From: ". $instance->getName () ." <". $instance->getEmail () .">\r\n";
			$headers .= "Reply-To: ". $instance->getEmail () ."\r\n";
			$headers .= "Content-Type: text/plain; charset=utf-8";

			set_error_handler ('logPhpError');
			
			$flag = mail ($email, '=?utf-8?B?'. base64_encode ($subject) .'?=', $msg, $headers);

			restore_error_handler ();

			if (!$flag)
				throw new Exception (__ ('The e-mail cannot be sent. Maybe the server has problems at moment! Please, try again more later.'));

			$message->addMessage (__ ('The message with the link to register the password was send for e-mail that you registered into website.'));

			$message->save ();

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function showMessages ()
	{
		$message = Message::singleton ();

		if (!is_object ($message) || !$message->has ())
			return FALSE;

		$str = '';
		while ($msg = $message->get ())
			$str .= $msg;

		$msgs = &XOAD_HTML::getElementById ('labelMessage');

		$msgs->innerHTML = '<div id="idMessage">'. $str .'</div>';

		$message->clear ();

		return TRUE;
	}

	public function delay ()
	{
		sleep (1);
	}

	public function xoadGetMeta()
	{
		XOAD_Client::mapMethods ($this, array ('delay', 'showMessages', 'logon', 'lostPassword'));

		XOAD_Client::publicMethods ($this, array ('delay', 'showMessages', 'logon', 'lostPassword'));

		XOAD_Client::privateMethods ($this, array ());
	}
}
?>