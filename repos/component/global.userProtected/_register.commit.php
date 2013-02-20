<?
$form =& Form::singleton (Security::singleton ()->getUserType ($section->getName ())->getRegister ());

if (!$form->recovery ($formData))
	throw new Exception (__ ('Unable to retrieve the data submitted!'));

require_once $instance->getCorePath () .'extra/captcha/captcha.php';

$captcha = new Securimage ();

if (!isset ($formData ['_TITAN_CAPTCHA_']) || !$captcha->check ($formData ['_TITAN_CAPTCHA_']))
	throw new Exception (__ ('You must correctly enter the string image of the end of the form on the indicated field. Try again!'));

$fields = $form->getFields ();

foreach ($fields as $key => $field)
	if (!$field->isReadOnly () && $field->isSavable ())
	{
		$columns [$key] = $field->getColumn ();
		$values [$key] = Database::toValue ($field);
	}

$db = Database::singleton ();

$userId = Database::nextId ('_user', '_id');

array_push ($columns, "_id", "_active");
array_push ($values, "'". $userId ."'", "'0'");

$type = $section->getName ();

array_push ($columns, "_type");
array_push ($values, "'". $type ."'");

$userType = Security::singleton ()->getUserType ($type);

$passwd = randomHash (13) .'_INVALID_HASH_'. randomHash (13);

array_push ($columns, "_password");
array_push ($values, "'". $passwd ."'");

$name  = $fields [array_search ('_name', $columns)]->getValue ();
$login = $fields [array_search ('_login', $columns)]->getValue ();
$email = $fields [array_search ('_email', $columns)]->getValue ();

if ($userType->useLdap ())
{
	$ldap = $userType->getLdap ();
	
	$form->createLdapUser ($ldap, $ldap->getEssentialInput ($login, $name, $email, randomHash (10), $userId));
}

$sql = "INSERT INTO ". $form->getTable () ." (". implode (", ", $columns) .") VALUES (". implode (", ", $values) .")";

$sth = $db->prepare ($sql);

$sth->execute ();

$fields = $form->getFields ();

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
	
	$_SESSION = array ();
	
	session_destroy ();
}
catch (PDOException $e)
{
	$message->addWarning (__('Unable to bind initial groups to the user. You should manually set the groups of the new user. [ [1] ]', $e->getMessage ()));
}

try
{
	$mail = Mail::singleton ();
	
	$mail->clear ();
	
	$mail->addTag ('_LABEL_', $section->getLabel ());
	$mail->addTag ('_ITEM_', $fields [array_search ('_name', $columns)]->getValue () .' (Login: '. $fields [array_search ('_login', $columns)]->getValue () .', E-mail: '. $fields [array_search ('_email', $columns)]->getValue () .')');
	$mail->addTag ('_LINK_', Instance::singleton ()->getUrl () .'titan.php?toSection='. $section->getName () .'&toAction=view&itemId='. $userId);
	
	$mail->send ('REGISTER');
}
catch (PDOException $e)
{
	$message->addWarning ($e->getMessage ());
}
catch (Exception $e)
{
	$message->addWarning ($e->getMessage ());
}

Log::singleton ()->add ('REGISTER', $form->getResume ($userId));
?>