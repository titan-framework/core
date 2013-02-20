<?
$form =& Form::singleton ('delete.xml', 'view.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!$form->delete ($itemId, FALSE))
	throw new Exception ('Não foi possível apagar o usuário!');

$action = $form->goToAction ('success');

$message->addMessage ('Usuário apagado com sucesso!');

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