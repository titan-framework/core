<?
class Ajax
{
	public function getBoxes ()
	{
		$message = Message::singleton ();
		
		try
		{
			$db = Database::singleton ();
			
			$user = User::singleton ();
			
			$sth = $db->prepare ("SELECT * FROM _rss WHERE _user = '". $user->getId () ."'");
			
			$sth->execute ();
			
			$str  = "";
			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
				$str .= "createARSSBox('". $obj->_url ."', ". $obj->_column_index .", ". ($obj->_height ? $obj->_height : "false") .", ". $obj->_number .", ". $obj->_minutes .", ". $obj->_id ."); ";
			
			return $str;
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
		
		return "";
	}
	
	public function saveFeed ($url, $height, $number, $minutes, $id = FALSE)
	{
		$message = Message::singleton ();
		
		try
		{
			$db = Database::singleton ();
			
			$user = User::singleton ();
			
			if ($id)
			{
				$sth = $db->prepare ("UPDATE _rss SET _url = '". $url ."', _height = '". $height ."', _number = '". $number ."', _minutes = '". $minutes ."' WHERE _id = '". $id ."' AND _user = '". $user->getId () ."'");
				
				$sth->execute ();
				
				return TRUE;
			}
			
			$sth = $db->prepare ("INSERT INTO _rss (_url, _height, _number, _minutes, _user) VALUES ('". $url ."', '". $height ."', '". $number ."', '". $minutes ."', '". $user->getId () ."')");
			
			$sth->execute ();
			
			return Database::lastId ('_rss');
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
	
	public function setColumn ($column, $id)
	{
		$message = Message::singleton ();
		
		try
		{
			$db = Database::singleton ();
			
			$user = User::singleton ();
			
			$sth = $db->prepare ("UPDATE _rss SET _column_index = ". $column ." WHERE _id = '". $id ."' AND _user = '". $user->getId () ."'");
			
			$sth->execute ();
			
			return TRUE;
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
	
	public function deleteFeed ($id)
	{
		$message = Message::singleton ();
		
		try
		{
			$db = Database::singleton ();
			
			$user = User::singleton ();
			
			$sth = $db->prepare ("DELETE FROM _rss WHERE _id = '". $id ."' AND _user = '". $user->getId () ."'");
			
			$sth->execute ();
			
			return TRUE;
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
	
	public function changePasswd ($password, $new)
	{
		$message = Message::singleton ();
		
		$return = TRUE;
		
		try
		{
			$validate = array ("'", '"', '\\', '--', '/*', '*/');
			
			if ($password !== str_replace ($validate, '', $password) || $new !== str_replace ($validate, '', $new))
				throw new Exception (__ ('Sequences of characters [[1]] may not be used in the password!', htmlspecialchars (implode (', ', $validate))));
			
			$db = Database::singleton ();
			
			$user = User::singleton ();
			
			if ($user->getType ()->useLdap ())
			{
				$ldap = $user->getType ()->getLdap ();
				
				$ldap->connect ($user->getLogin (), $password);
				
				$info = $ldap->getEssentialPassword ($user->getLogin (), $new);
				
				$ldap->update ($info, $user->getLogin ());
				
				$ldap->close ();
			}
			else
			{
				if (!Security::singleton ()->encryptOnClient ())
				{
					$password = sha1 ($password);
					
					$new = sha1 ($new);
				}
				
				$sth = $db->prepare ("SELECT * FROM _user WHERE _login = :login AND _id = :id AND _password = :passwd AND _deleted = '0'");
				
				$sth->bindValue (':login', $user->getLogin (), PDO::PARAM_STR);
				$sth->bindValue (':id', $user->getId (), PDO::PARAM_INT);
				$sth->bindValue (':passwd', $password, PDO::PARAM_STR);
				
				$sth->execute ();
				
				$obj = $sth->fetch (PDO::FETCH_OBJ);
				
				if (!$obj)
					throw new Exception (__ ('The current password you entered is incorrect!'));
				
				$sth = $db->prepare ("UPDATE _user SET _password = :passwd WHERE _id = :id");
				
				$sth->bindValue (':passwd', $new, PDO::PARAM_STR);
				$sth->bindValue (':id', $user->getId (), PDO::PARAM_INT);
				
				if (!$sth->execute ())
					throw new Exception (__ ('It was not possible to change the password.'));
			}
				
			$message->addMessage (__ ('Password successfully changed.'));
			
			Log::singleton ()->add ('PASSWORD');
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
	
	public function makeUpdate ()
	{
		$msg = '';
		
		try
		{
			$array = update (TRUE);
			
			foreach ($array as $value)
				$msg .= $this->makeAlert ($value [0], $value [1]);
		}
		catch (Exception $e)
		{
			$msg .= $this->makeAlert ('FAIL', $e->getMessage ());
		}
		
		return $msg;
	}
	
	private function makeAlert ($type, $message)
	{
		switch ($type)
		{
			case 'SUCCESS':
				$color = '009900';
				$img = 'ok';
				break;
			
			case 'FAIL':
				$color = '990000';
				$img = 'cancel';
				break;
			
			case 'WARNING':
				$color = 'E4B01A';
				$img = 'alert';
				break;
			
			default:
				return '';
		}
		
		$str  = '<table width="100%" style="border: #'. $color .' 1px solid; margin-bottom: 3px;">';
		$str .= '	<tr height="30px">';
		$str .= '		<td style="text-align: center; width: 30px;"><img src="titan.php?target=loadFile&file=interface/icon/'. $img .'.gif" border="0" /></td>';
		$str .= '		<td>'. $message .'</td>';
		$str .= '	</tr>';
		$str .= '</table>';
		
		return $str;
	}
	
	public function disconnectFromSocialNetwork ($name)
	{
		$message = Message::singleton ();
		
		$return = TRUE;
		
		try
		{
			if (!Social::isActive ())
				throw new Exception (__ ('Social networks are not enableds!'));
			
			$driver = Social::singleton ()->getSocialNetwork ($name);
			
			if (!$driver)
				throw new Exception (__ ('Invalid social network!'));
			
			$sth = Database::singleton ()->prepare ("UPDATE _user SET ". $driver->getIdColumn () ." = NULL WHERE _id = :id");
			
			$sth->bindParam (':id', User::singleton ()->getId (), PDO::PARAM_INT);
			
			$sth->execute ();
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
	
	public function registerDevice ($name)
	{
		$message = Message::singleton ();
		
		$return = 'array = new Array ();';
		
		try
		{
			if (!MobileDevice::isActive ())
				throw new Exception (__ ('Syncronization with mobile devices is not enabled!'));
			
			$registered = MobileDevice::register ($name);
			
			return "array = new Array ('". $registered->name ."', '". $registered->id ."', '". MobileDevice::formatPrivateKey ($registered->pk) ."')";
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
	
	public function unregisterDevice ($id)
	{
		$message = Message::singleton ();
		
		$return = FALSE;
		
		try
		{
			if (!MobileDevice::isActive ())
				throw new Exception (__ ('Syncronization with mobile devices is not enabled!'));
			
			$return = MobileDevice::unregister ($id);
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
	
	public function delay ()
	{
		sleep (1);
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
	
	public function xoadGetMeta ()
	{
		$methods = get_class_methods ($this);

		XOAD_Client::mapMethods ($this, $methods);

		XOAD_Client::publicMethods ($this, $methods);

		XOAD_Client::privateMethods ($this, array ());
	}
}
?>