<?php
$form = new Form ('area/visualizar.xml');

if (!$form->delete ($itemId))
	throw new Exception ('Não foi possível apagar o area!');

$message->addMessage ('Area apagado com sucesso!');
?>