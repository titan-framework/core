<?
$form =& Form::singleton ('create.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!$form->recovery ())
	throw new Exception ('Não foi possível recuperar os dados submetidos!');

if (!$form->save ())
	throw new Exception ('Não foi possível salvar os dados submetidos!');

$itemId = Database::lastId ($form->getTable ());

$action = $form->goToAction ('success');

$message->addMessage ('Dados salvos com sucesso!');

Log::singleton ()->add ('CREATE', $form->getResume ($itemId));
?>