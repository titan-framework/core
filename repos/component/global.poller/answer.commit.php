<?php
$form =& Form::singleton ('answer.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!isset ($_POST['resposta']))
	throw new Exception ('Houve perda de variáveis!');

$resposta = $_POST['resposta'];

try
{
	$db = Database::singleton ();
	
	$db->beginTransaction ();
	
	$db->exec ("DELETE FROM ". $form->getTable () ."_answer WHERE _poller = '". $itemId ."'");
	
	$sth = $db->prepare ("INSERT INTO ". $form->getTable () ."_answer (_poller, _order, _label) VALUES ('". $itemId ."', :order, :label)");
	
	$count = 1;
	foreach ($resposta as $trash => $resp)
		$sth->execute (array (':order' => $count++, ':label' => $resp));
	
	$db->commit ();
	
	$message->addMessage ('Respostas gravadas com sucesso!');
	
	$action = $form->goToAction ('success');
	
	Log::singleton ()->add ('Respostas de enquete editadas [Enquete: '. $itemId .'].');
}
catch (PDOException $e)
{
	$db->rollBack ();
	
	$message->addWarning ('Impossível salvar as respostas: '. $e->getMessage ());
}
?>