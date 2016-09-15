<?php
$form =& FormSimple::singleton ('edit.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!$form->recovery ())
	throw new Exception ('Não foi possível recuperar os dados submetidos!');

$id = $section->getDirective ('_ID_') ? $section->getDirective ('_ID_') : 'SIMPLE_'. $section->getName ();

if (!$form->save ($id))
	throw new Exception ('Não foi possível salvar os dados submetidos!');

$action = $form->goToAction ('success');

$message->addMessage ('Dados modificados com sucesso!');

try
{
	$mail = Mail::singleton ();
	
	$mail->clear ();
	
	$mail->addTag ('_LABEL_', $section->getLabel ());
	$mail->addTag ('_ACTOR_', User::singleton ()->getName ());
	$mail->addTag ('_LINK_', Instance::singleton ()->getUrl () .'titan.php?toSection='. $section->getName () .'&toAction=view');
	
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