<?
$form =& Form::singleton ('create.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!$form->recovery ())
	throw new Exception (__ ('Unable to retrieve the data submitted!'));

$fields = $form->getFields ();

$columns = array ();
$values  = array ();

foreach ($fields as $key => $field)
	if (!$field->isReadOnly () && $field->isSavable ())
	{
		$columns [$key] = $field->getColumn ();
		$values [$key]  = Database::toValue ($field);
	}

$userId = Database::nextId ('_user', '_id');

array_push ($columns, "_id");
array_push ($values, "'". $userId ."'");

if (!in_array ('_type', $columns))
{
	$type = $section->getName ();

	array_push ($columns, "_type");
	array_push ($values, "'". $type ."'");
}
else
	$type = $fields [array_search ('_type', $columns)]->getValue ();

$userType = Security::singleton ()->getUserType ($type);

$passwd = randomHash (13) ."_INVALID_HASH_". randomHash (13);

array_push ($columns, "_password");
array_push ($values, "'". $passwd ."'");

if (!in_array ('_active', $columns))
{
	array_push ($columns, "_active");
	array_push ($values, "'1'");
}

$name  = $fields [array_search ('_name', $columns)]->getValue ();
$login = $fields [array_search ('_login', $columns)]->getValue ();
$email = $fields [array_search ('_email', $columns)]->getValue ();

if ($userType->useLdap ())
{
	$ldap = $userType->getLdap ();
	
	$form->createLdapUser ($login, $ldap, $ldap->getEssentialInput ($login, $name, $email, randomHash (10), $userId));
}

$db = Database::singleton ();

$sql = "INSERT INTO ". $form->getTable () ." (". implode (", ", $columns) .") VALUES (". implode (", ", $values) .")";

$sth = $db->prepare ($sql);

$sth->execute ();

foreach ($fields as $key => $field)
	if (!$field->isSavable ())
		$field->save ($userId);

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
	$message->addWarning (__('Unable to bind initial groups to the user. You should manually set the groups of the new user. [ [1] ]', $e->getMessage ()));
}

$message->addMessage (__('User registered with success!'));

$action = $form->goToAction ('success');

$mail = Mail::singleton ();

$subject = $mail->getCreate ('subject');

$msg = $mail->getCreate ('text');

$instance = Instance::singleton ();

$headers  = "From: ". $instance->getName () ." <". $instance->getEmail () .">\r\n";
$headers .= "Reply-To: ". $instance->getEmail ();

$hash = Security::singleton ()->getHash ();

if ($userType->useLdap ())
{
	$ldap = $userType->getLdap ();
	
	$ldap->connect (FALSE, FALSE, TRUE);
	
	$result = $ldap->load ($login, array ('userPassword'));
	
	$ldap->close ();
	
	$passwd = $result ['userpassword'];
}

if ($instance->getFriendlyUrl ('change-password') == '')
	$link = $instance->getUrl () ."titan.php?target=remakePasswd&login=". urlencode ($login) ."&hash=". shortlyHash (sha1 ($hash . $name . $hash . $passwd . $hash . $email . $hash));
else
	$link = $instance->getUrl () . $instance->getFriendlyUrl ('change-password') ."/". urlencode ($login) ."/". shortlyHash (sha1 ($hash . $name . $hash . $passwd . $hash . $email . $hash));

$search  = array ('[USER]', '[NAME]', '[LINK]', '[LOGIN]');
$replace = array ($name, html_entity_decode (Instance::singleton ()->getName ()), $link, $login);

$subject = str_replace ($search, $replace, $subject);
$msg = str_replace ($search, $replace, $msg);

if (@mail ($email, $subject, $msg, $headers) )
	$message->addMessage (__('A link to register the password was sent to the e-mail of the new user.'));
else
	$message->addWarning (__('Unable to send the e-mail with the link to register a password. This link can be obtained through the option "Forgogot my password", at logon page.'));

try
{
	$mail = Mail::singleton ();
	
	$mail->clear ();
	
	$mail->addTag ('_LABEL_', $section->getLabel ());
	$mail->addTag ('_ACTOR_', User::singleton ()->getName ());
	$mail->addTag ('_ITEM_', $toName .' (Login: '. $login .', E-mail: '. $toEmail .')');
	$mail->addTag ('_LINK_', Instance::singleton ()->getUrl () .'titan.php?toSection='. $section->getName () .'&toAction=view&itemId='. $userId);
	
	$mail->send ('CREATE');
}
catch (PDOException $e)
{
	$message->addWarning ($e->getMessage ());
}
catch (Exception $e)
{
	$message->addWarning ($e->getMessage ());
}

Log::singleton ()->add ('CREATE', $form->getResume ($userId));
?>