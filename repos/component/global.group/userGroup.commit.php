<?
$form =& Form::singleton ('user.xml');

$action = $form->goToAction ('fail');

if (!isset ($_POST['systemIdHidden']))
	throw new Exception ('Perda de variáveis!');

if ($_POST['systemIdHidden'])
{
	$itemId = $_POST['systemIdHidden'];
	
	if (isset ($_POST['selectUsersFromSystem']))
		$arrayForSave = $_POST['selectUsersFromSystem'];
	else
		$arrayForSave = array ();
	
	try
	{
		$db = Database::singleton ();
		
		$db->beginTransaction ();
		
		$db->exec ("DELETE FROM _user_group WHERE _group = '". $itemId ."'");
		
		$sth = $db->prepare ("INSERT INTO _user_group (_user, _group) VALUES (:user, '". $itemId ."')");
	
		foreach ($arrayForSave as $trash => $auxUser)
			$sth->execute (array (':user' => $auxUser));
		
		$db->commit ();
		
		$message->addMessage ('Usuários vinculados ao Grupo com sucesso!');
	
		$user->setGroups ();
	}
	catch (PDOException $e)
	{
		$db->rollBack ();
		
		$message->addWarning ('Impossível vincular Usuários ao Grupo: '. $e->getMessage ());
	}
}

$itemId = isset ($_POST['selectSystems']) ? $_POST['selectSystems'] : 0;

Log::singleton ()->add ('Nova configuração para usuários vinculados a grupos de usuários [Grupo: '. $itemId .'].');
?>