<?php
/**
 * Use ajax to change user password.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage ajax
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see User, Xoad, AjaxLogon
 */
class AjaxPasswd
{
	public function changePasswd ($hash, $password, $login)
	{
		$message = Message::singleton ();

		try
		{
			$validate = array ("'", '"', '\\', '--', '/*', '*/');
			$validLogin = str_replace ($validate, '', $login);
			$validPassword = str_replace ($validate, '', $password);

			if ($login !== $validLogin)
				throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

			if ($password !== $validPassword)
				throw new Exception (__ ('Invalid Password! The system cannot permit using special characters: \', ", \\, --, /*, */.'));

			if (Security::singleton ()->encryptOnClient () && strlen ($password) != 40)
				throw new Exception (__ ('Invalid Password! Verify if JavaScript support is active in your browser.'));

			$db = Database::singleton ();

			$sth = $db->prepare ("SELECT _name, _email, _password, _id, _type FROM _user WHERE _login = :login AND _deleted = '0' AND _active = '1'");

			$sth->bindValue (':login', $login, PDO::PARAM_STR);

			$sth->execute ();

			$obj = $sth->fetch (PDO::FETCH_OBJ);

			if (!$obj)
				throw new Exception (__ ('Non-existent, Deleted or Inactive User!'));

			$name   = $obj->_name;
			$email  = $obj->_email;
			$passwd = $obj->_password;

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

			$systemHash = Security::singleton ()->getHash ();

			$vHash = sha1 ($systemHash . $name . $systemHash . $passwd . $systemHash . $email . $systemHash);

			if ((strlen ($hash) != 10 && $hash != $vHash) || (strlen ($hash) != 40 && $hash != shortlyHash ($vHash)))
				throw new Exception (__ ('Invalid Hash! Use the link "Recovery Password" at the login page for receive a valid hash into your e-mail.'));

			if (Security::singleton ()->getUserType ($obj->_type)->useLdap ())
			{
				$ldap->connect (FALSE, FALSE, TRUE);

				$fields = $ldap->getEssentialPassword ($login, $password);

				$ldap->update ($fields, $login);

				$ldap->close ();
			}
			else
			{
				if (!Security::singleton ()->encryptOnClient ())
					$password = sha1 ($password);

				$sth = $db->prepare ("UPDATE _user SET _password = :passwd WHERE _id = :id");

				$sth->bindValue (':id', $obj->_id, PDO::PARAM_INT);
				$sth->bindValue (':passwd', $password, PDO::PARAM_STR);

				if (!$sth->execute ())
					throw new Exception (__ ('Unable to change the password.'));
			}

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

	public function xoadGetMeta()
	{
		XOAD_Client::mapMethods ($this, array ('showMessages', 'delay', 'changePasswd'));

		XOAD_Client::publicMethods ($this, array ('showMessages', 'delay', 'changePasswd'));

		XOAD_Client::privateMethods ($this, array ());
	}
}
