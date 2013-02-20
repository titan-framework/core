<?
$form =& Form::singleton ('delete.xml', 'view.xml', 'all.xml');

$action = $form->goToAction ('fail');

$db = Database::singleton ();

$sth = $db->prepare ("SELECT _admin FROM _group WHERE _id = '". $itemId ."'");

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if ((int) $obj->_admin)
	throw new Exception ('Você esta tentando apagar um grupo com status de Administrador. Para fazer isto primeiramente você deve retirar este status do grupo, editando-o.');

$resume = $form->getResume ($itemId);

if (!$form->delete ($itemId))
	throw new Exception ('Não foi possível apagar o grupo!');

$action = $form->goToAction ('success');

$message->addMessage ('Grupo apagado com sucesso!');

Log::singleton ()->add ('DELETE', $resume);
?>