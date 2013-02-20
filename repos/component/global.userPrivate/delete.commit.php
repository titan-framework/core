<?
$form =& Form::singleton ('delete.xml', 'view.xml', 'all.xml');

$action = $form->goToAction ('fail');

if ($itemId == $user->getId ())
	throw new Exception (__('You can not delete you own user!'));

$section = Business::singleton ()->getSection (Section::TCURRENT);

$userType = Security::singleton ()->getUserType ($section->getName ());

if ($userType->useLdap ())
{
	if (!$form->load ($itemId))
		throw new Exception (__('Unable to load the user data!'));

	if (!$form->deleteFromLdap ($userType->getLdap ()))
		throw new Exception (__('Unable to delete the user data from LDAP server!'));
}

if (!$form->delete ($itemId, FALSE))
	throw new Exception (__('Unable to delete the user!'));

$action = $form->goToAction ('success');

$message->addMessage (__('User deleted with success!'));

try
{
	$mail = Mail::singleton ();

	$mail->clear ();

	$mail->addTag ('_LABEL_', $section->getLabel ());
	$mail->addTag ('_ACTOR_', User::singleton ()->getName ());
	$mail->addTag ('_RESUME_', $form->getResume ($itemId));

	$mail->send ('DELETE');
}
catch (PDOException $e)
{
	$message->addWarning ($e->getMessage ());
}
catch (Exception $e)
{
	$message->addWarning ($e->getMessage ());
}

Log::singleton ()->add ('DELETE', $form->getResume ($itemId));
?>