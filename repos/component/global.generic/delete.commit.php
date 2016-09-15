<?php
$form =& Form::singleton ('delete.xml', 'view.xml', 'all.xml');

$action = $form->goToAction ('fail');

$resume = $form->getResume ($itemId);

if (!$form->delete ($itemId, TRUE))
	throw new Exception (__ ('Unable to delete the item!'));

$action = $form->goToAction ('success');

$message->addMessage (__ ('Item deleted with success!'));

try
{
	$mail = Mail::singleton ();

	$mail->clear ();

	$mail->addTag ('_LABEL_', $section->getLabel ());
	$mail->addTag ('_ACTOR_', User::singleton ()->getName ());
	$mail->addTag ('_RESUME_', $resume);

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

Log::singleton ()->add ('DELETE', $resume);
?>