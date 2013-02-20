<?
$form =& Form::singleton ('edit.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!$form->recovery ())
	throw new Exception (__ ('Unable to retrieve the data submitted!'));

$type = Business::singleton ()->getSection (Section::TCURRENT)->getName ();

$userType = Security::singleton ()->getUserType ($type);

if ($userType->useLdap ())
	if (!$form->saveOnLdap ($userType->getLdap ()))
		throw new Exception (__ ('Unable to save the data into LDAP server!'));

if (!$form->save ($itemId))
	throw new Exception (__ ('Unable to save the data submitted!'));

$action = $form->goToAction ('success');

$message->addMessage (__ ('Data modified with success!'));

try
{
	$fields = $form->getFields ();
	
	$columns = array ();
	
	foreach ($fields as $key => $field)
		if (!$field->isReadOnly () && $field->isSavable ())
			$columns [$field->getColumn ()] = $field;
	
	if ($userType->useLdap () || !array_key_exists ('_type', $columns) || $columns ['_type']->isReadOnly () || $columns ['_type']->getValue () == Business::singleton ()->getSection (Section::TCURRENT)->getName ())
		throw new Exception ();

	$db = Database::singleton ();

	$db->beginTransaction ();

	$db->exec ("DELETE FROM _user_group WHERE _user = '". $itemId ."'");

	$sql = "SELECT _group FROM _type_group WHERE _type = ". Database::toValue ($columns ['_type']);

	$sth = $db->prepare ($sql);

	$sth->execute ();

	$sthUser = $db->prepare ("INSERT INTO _user_group (_user, _group) VALUES ('". $itemId ."', :group)");

	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		$sthUser->execute (array (':group' => $obj->_group));

	$db->commit ();

	$user->setGroups ();

	$message->addMessage (__ ('Attention! As the type of user was modified it was released from groups of which belonged and it was bound into default group permission of the your new type.'));
}
catch (PDOException $e)
{
	$db->rollBack ();

	$message->addWarning (__ ('Unable to bind new type of default groups to the user. You should manually set the groups of new user or it will already continue with the same permissions. [ [1] ]', $e->getMessage ()));
}
catch (Exception $e)
{}

try
{
	$mail = Mail::singleton ();

	$mail->clear ();
	
	$mail->addTag ('_LABEL_', $section->getLabel ());
	$mail->addTag ('_ACTOR_', User::singleton ()->getName ());
	$mail->addTag ('_ITEM_', $columns ['_name']->getValue () .' (Login: '. $columns ['_login']->getValue () .', E-mail: '. $columns ['_email']->getValue () .')');
	$mail->addTag ('_LINK_', Instance::singleton ()->getUrl () .'titan.php?toSection='. $section->getName () .'&toAction=view&itemId='. $itemId);

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

Log::singleton ()->add ('EDIT', $form->getResume ($itemId));
?>