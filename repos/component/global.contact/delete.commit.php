<?php
$form =& Form::singleton ('delete.xml', 'view.xml', 'all.xml');

$action = $form->goToAction ('fail');

$resume = $form->getResume ($itemId);

if (!$form->delete ($itemId))
	throw new Exception ('Não foi possível apagar o item!');

$action = $form->goToAction ('success');

$message->addMessage ('Item apagado com sucesso!');

Log::singleton ()->add ('DELETE', $resume);
?>