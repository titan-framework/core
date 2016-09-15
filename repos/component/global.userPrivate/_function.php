<?php
function ldapUpdate ($itemId = 0)
{
	if ((int) $itemId && isset ($_SESSION ['_TITAN_LDAP_USER_CONTROL_'][$itemId]) && (bool) $_SESSION ['_TITAN_LDAP_USER_CONTROL_'][$itemId])
		return TRUE;
	
	$section = Business::singleton ()->getSection (Section::TCURRENT);

	if (!Security::singleton ()->getUserType ($section->getName ())->useLdap ())
		return TRUE;

	if (!Security::singleton ()->getUserType ($section->getName ())->getLdap ()->updateOnLogon ())
		return TRUE;

	$userType = Security::singleton ()->getUserType ($section->getName ());

	$db = Database::singleton ();

	if ($itemId)
	{
		$sth = $db->prepare ("SELECT _login, _name FROM _user WHERE _type = '". $section->getName () ."' AND _deleted = '0' AND _id = '". $itemId ."'");

		$sth->execute ();

		$user = $sth->fetch (PDO::FETCH_OBJ);

		if (!$user)
			throw new Exception (__ ('Unable to recover user data from database!'));

		$form = new Form (array ($userType->getLdapForm (), 'edit.xml', 'all.xml'));

		if (!$form->load ($itemId))
			throw new Exception (__ ('Unable to load your private data from user [1] [ [2] ]', $user->_name, $user->_login));

		if (!$form->loadFromLdap ($user->_login, $userType->getLdap ()))
			throw new Exception (__ ('Unable to load LDAP server data to the user [1] [ [2] ]', $user->_name, $user->_login));

		if (!$form->save ($itemId, FALSE))
			throw new Exception (__ ('Unable to save the database data from LDAP server to the user [1] [ [2] ]', $user->_name, $user->_login));
		
		$_SESSION ['_TITAN_LDAP_USER_CONTROL_'][$itemId] = TRUE;
	}
	else
	{
		$ldap = $userType->getLdap ();
		
		$ldap->connect (FALSE, FALSE, TRUE);
		
		$all = $ldap->search (array ('uid', 'displayname', 'cn', 'givenname', 'mail'), '(|(cn=*))');
		
		$form = new Form (array ($userType->getLdapForm (), $userType->getModify (), 'edit.xml', 'all.xml'));
		
		foreach ($all as $trash => $entry)
		{
			if (!is_array ($entry))
				continue;
			
			$info = array ();
			
			foreach ($entry as $key => $value)
			{
				if (is_numeric ($key) || !is_array ($value) || !array_key_exists (0, $value))
					continue;
				
				$info [trim ($key)] = trim ($value [0]);
			}
			
			$login = $info ['uid'];
			
			$nameValidate = array ('cn', 'displayname', 'givenname');
				
			$name = $login;
			
			foreach ($nameValidate as $trash => $value)
			{
				if (!isset ($info [$value]) || trim ($info [$value]) == '')
					continue;
				
				$name = $info [$value];
				
				break;
			}
			
			$sth = $db->prepare ("SELECT _login, _id, _name FROM _user WHERE _login = '". $login ."'");
	
			$sth->execute ();
			
			$user = $sth->fetch (PDO::FETCH_OBJ);
			
			try
			{
				$form->setLoad (FALSE);
				
				$userId = 0;
				
				if (is_object ($user))
				{
					$userId = $user->_id;
					
					if (isset ($_SESSION ['_TITAN_LDAP_USER_CONTROL_'][$user->_id]) && (bool) $_SESSION ['_TITAN_LDAP_USER_CONTROL_'][$user->_id])
						continue;
					
					if (!$form->load ($user->_id))
						throw new Exception (__ ('Unable to load your private data from user [1] [ [2] ]', $user->_name, $user->_login));
				}
				else
				{
					$userId = Database::nextId ('_user', '_id');
					
					$fields = array ('_id' 	 	 => "'". $userId ."'",
									 '_login' 	 => "'". $login ."'",
									 '_name'	 => "'". $name ."'",
									 '_email'	 => "'". (isset ($info ['mail']) ? trim ($info ['mail']) : '') ."'",
									 '_password' => "'". randomHash (13) .'_INVALID_HASH_'. randomHash (13) ."'",
									 '_active'	 => "B'1'",
									 '_deleted'	 => "B'0'",
									 '_type'	 => "'". $userType->getName () ."'");
					
					$sql = "INSERT INTO _user (". implode (", ", array_keys ($fields)) .") VALUES (". implode (", ", $fields) .")";
					
					$sth = $db->prepare ($sql);
					
					$sth->execute ();
					
					try
					{
						$sql = "SELECT _group FROM _type_group WHERE _type = '". $userType->getName () ."'";
					
						$sth = $db->prepare ($sql);
					
						$sth->execute ();
					
						$sthUser = $db->prepare ("INSERT INTO _user_group (_user, _group) VALUES ('". $userId ."', :group)");
					
						while ($obj = $sth->fetch (PDO::FETCH_OBJ))
							$sthUser->execute (array (':group' => $obj->_group));
					}
					catch (PDOException $e)
					{
						toLog (__('Unable to bind initial groups to the user. You should manually set the groups of the new user. [ [1] ]', $e->getMessage ()));
					}
				}
				
				if (!$form->loadFromLdap ($login, $ldap))
					throw new Exception (__ ('Unable to load LDAP server data to the user [1] [ [2] ]', $name, $login));
	
				if (!$form->save ($userId, FALSE))
					throw new Exception (__ ('Unable to save the database data from LDAP server to the user [1] [ [2] ]', $name, $login));
				
				$_SESSION ['_TITAN_LDAP_USER_CONTROL_'][$userId] = TRUE;
			}
			catch (Exception $e)
			{
				toLog ($e->getMessage ());
			}
			catch (PDOException $e)
			{
				toLog ($e->getMessage ());
			}
		}
		
		$ldap->close ();
	}

	return TRUE;
}
?>