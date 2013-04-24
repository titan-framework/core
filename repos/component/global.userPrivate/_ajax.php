<?
class Ajax
{
	public function isActive ($id)
	{
		$message = Message::singleton ();
		
		try
		{
			$db = Database::singleton ();
			
			$sth = $db->prepare ("SELECT _active FROM _user WHERE _id = ". $id);
			
			$sth->execute ();
			
			$obj = $sth->fetch (PDO::FETCH_OBJ);
			
			if ((int) $obj->_active)
				return TRUE;
			
			return FALSE;
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		
		$message->save ();
		
		$this->showMessages ();
		
		return FALSE;
	}

	public function activate ($id, $value)
	{
		$message = Message::singleton ();
		
		$return = TRUE;
		
		try
		{
			$db = Database::singleton ();
			
			$sth = $db->prepare ("UPDATE _user SET _active = '". $value ."' WHERE _id = ". $id);
			
			$sth->execute ();
			
			$message->addMessage ('Usuário '. ($value ? 'ATIVADO' : 'DESATIVADO') .' com sucesso!');
			
			if ($value)
			{
				$sth = $db->prepare ("SELECT _name, _login, _email, _type, _password FROM _user WHERE _id = ". $id);
				
				$sth->execute ();
				
				$obj = $sth->fetch (PDO::FETCH_OBJ);
				
				$toName  = $obj->_name;
				$toLogin = $obj->_login;
				$toEmail = $obj->_email;
				
				if (Security::singleton ()->getUserType ($obj->_type)->useLdap ())
				{
					$ldap = Security::singleton ()->getUserType ($obj->_type)->getLdap ();
					
					$ldap->connect (FALSE, FALSE, TRUE);
	
					$result = $ldap->load ($toLogin, array ('userPassword'));
					
					$passwd = $result ['userpassword'];
	
					$ldap->close ();
				}
				else
					$passwd = $obj->_password;
				
				$instance = Instance::singleton ();
				
				$headers  = "From: ". $instance->getName () ." <". $instance->getEmail () .">\r\n";
				$headers .= "Reply-To: ". $instance->getEmail () ."\r\n";
				$headers .= "Content-Type: text/plain; charset=utf-8";
				
				$mail = Mail::singleton ();
				
				$subject = $mail->getRegister ('subject');
				
				$msg = $mail->getRegister ('text');
				
				$hash = Security::singleton ()->getHash ();
				
				if ($instance->getFriendlyUrl ('change-password') == '')
					$link = $instance->getUrl () ."titan.php?target=remakePasswd&login=". urlencode ($toLogin) ."&hash=". shortlyHash (sha1 ($hash . $toName . $hash . $passwd . $hash . $toEmail . $hash));
				else
					$link = $instance->getUrl () . $instance->getFriendlyUrl ('change-password') ."/". urlencode ($toLogin) ."/". shortlyHash (sha1 ($hash . $toName . $hash . $passwd . $hash . $toEmail . $hash));
				
				$search  = array ('[USER]', '[NAME]', '[LINK]', '[LOGIN]');
				$replace = array ($toName, html_entity_decode ($instance->getName ()), $link, $toLogin);
				
				$subject = str_replace ($search, $replace, $subject);
				$msg = str_replace ($search, $replace, $msg);
				
				if (@mail ($toEmail, $subject, $msg, $headers))
					$message->addMessage ('Um link para cadastro de senha foi enviado para o e-mail do usuário.');
				else
					$message->addWarning ('Não foi possível enviar o e-mail com o link para cadastro de senha. Este link poderá ser obtido através da opção "Esqueci minha senha", na página de logon.');
				
				Log::singleton ()->add ('O usuário '. $toName .' ('. $toLogin .') foi '. ($value ? 'ATIVADO' : 'DESATIVADO') .'.');
			}
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
			
			$return = FALSE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
			
			$return = FALSE;
		}
		
		$message->save ();
		
		$this->showMessages ();
		
		return $return;
	}
	
	public function changePasswd ($userId, $password)
	{
		$message = Message::singleton ();
		
		$return = TRUE;
		
		try
		{
			if (!User::singleton ()->hasPermission ('_CHANGE_PASSWORD_'))
				throw new Exception ('You do not have permission to change passwords!');
			
			if (!is_integer ($userId) || !$userId)
				throw new Exception ('Invalid parameters!');
			
			$validate = array ("'", '"', '\\', '--', '/*', '*/');
			
			if ($password !== str_replace ($validate, '', $password))
				throw new Exception (__ ('Sequences of characters [[1]] may not be used in the password!', htmlspecialchars (implode (', ', $validate))));
			
			$db = Database::singleton ();
			
			$sth = $db->prepare ("SELECT _login, _type, _email FROM _user WHERE _id = :id");
			
			$sth->bindParam (':id', $userId, PDO::PARAM_INT);
			
			$sth->execute ();
			
			$user = $sth->fetch (PDO::FETCH_OBJ);
			
			if (!$user)
				throw new Exception ('Invalid user!');
			
			if (Security::singleton ()->getUserType ($user->_type)->useLdap ())
			{
				$ldap = Security::singleton ()->getUserType ($user->_type)->getLdap ();
				
				$ldap->connect (FALSE, FALSE, TRUE);
				
				$info = $ldap->getEssentialPassword ($user->_login, $password);
				
				$ldap->update ($info, $user->_login);
				
				$ldap->close ();
			}
			else
			{
				if (!Security::singleton ()->encryptOnClient ())
					$password = sha1 ($password);
				
				$sth = $db->prepare ("UPDATE _user SET _password = :passwd WHERE _id = :id");
				
				$sth->bindValue (':passwd', $password, PDO::PARAM_STR);
				$sth->bindValue (':id', $userId, PDO::PARAM_INT);
				
				if (!$sth->execute ())
					throw new Exception (__ ('It was not possible to change the password.'));
			}
				
			$message->addMessage (__ ('Password successfully changed.'));
			
			Log::singleton ()->add ('PASSWORD');
			
			Alert::add ('_PASSWORD_CHANGED_', $userId, array ($userId), array ('[LOGIN]' => $user->_login, '[PASSWD]' => $password));
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
			
			$return = FALSE;
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
			
			$return = FALSE;
		}
		
		$message->save ();
		
		return $return;
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
		XOAD_Client::mapMethods ($this, array ('showMessages', 'delay', 'isActive', 'activate', 'changePasswd'));

		XOAD_Client::publicMethods ($this, array ('showMessages', 'delay', 'isActive', 'activate', 'changePasswd'));
		
		XOAD_Client::privateMethods ($this, array ());
	}
}
?>