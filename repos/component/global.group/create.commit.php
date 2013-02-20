<?
$form =& Form::singleton ('create.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!$form->recovery ())
	throw new Exception ('Não foi possível recuperar os dados submetidos!');

$itemId = $form->save (0, FALSE);

if (!$itemId)
	throw new Exception ('Não foi possível salvar os dados submetidos!');

$action = $form->goToAction ('success');

$message->addMessage ('Grupo salvo com sucesso!');

Log::singleton ()->add ('CREATE', $form->getResume ($itemId));
?>