<?
$form =& Form::singleton ('edit.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!$form->recovery ())
	throw new Exception ('Não foi possível recuperar os dados submetidos!');

if (!$form->save ($itemId))
	throw new Exception ('Não foi possível salvar os dados submetidos!');

$action = $form->goToAction ('success');

$message->addMessage ('Dados modificados com sucesso!');

Log::singleton ()->add ('EDIT', $form->getResume ($itemId));
?>