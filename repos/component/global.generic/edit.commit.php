<?php
$form =& Form::singleton ('edit.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!$form->recovery ())
	throw new Exception (__ ('Unable to retrieve the data submitted!'));

if (!$form->save ($itemId))
	throw new Exception (__ ('Unable to save the data submitted!'));

$action = $form->goToAction ('success');

$message->addMessage (__ ('Data modified with success!'));

try
{
	$mail = Mail::singleton ();

	$mail->clear ();

	$mail->addTag ('_LABEL_', $section->getLabel ());
	$mail->addTag ('_ACTOR_', User::singleton ()->getName ());
	$mail->addTag ('_LINK_', Instance::singleton ()->getUrl () .'titan.php?toSection='. $section->getName () .'&toAction=view&itemId='. $itemId);

	$mail->send ('EDIT');
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