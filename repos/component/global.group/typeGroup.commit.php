<?php
$form =& Form::singleton ('type.xml');

$action = $form->goToAction ('fail');

if (!isset ($_POST['typeIdHidden']))
	throw new Exception ('Perda de variáveis!');

if ($_POST['typeIdHidden'])
{
	$itemId = $_POST['typeIdHidden'];
	
	if (isset ($_POST['selectSystemsFromType']))
		$arrayForSave = $_POST['selectSystemsFromType'];
	else
		$arrayForSave = array ();
	
	try
	{
		$db = Database::singleton ();
		
		$db->beginTransaction ();
		
		$db->exec ("DELETE FROM _type_group WHERE _type = '". $itemId ."'");
		
		$sth = $db->prepare ("INSERT INTO _type_group (_type, _group) VALUES ('". $itemId ."', :group)");
	
		foreach ($arrayForSave as $trash => $group)
			$sth->execute (array (':group' => $group));
		
		$db->commit ();
		
		$message->addMessage ('Grupos vinculados ao Tipo de Usuário com sucesso! Novos usuários deste tipo cadastrados no sistema serão vinculados automaticamente aos grupos correspondentes.');
	
		foreach ($arrayForSave as $trash => $group)
		{
			try
			{
				$sth = $db->prepare ("SELECT _id FROM _user WHERE _type = '". $itemId ."' AND _id NOT IN (SELECT _user FROM _user_group WHERE _group = '". $group ."')"); 
				
				$sth->execute ();
				
				$sthInsert = $db->prepare ("INSERT INTO _user_group (_user, _group) VALUES (:user, '". $group ."')");
			
				while ($obj = $sth->fetch (PDO::FETCH_OBJ))
					$sth->execute (array (':user' => $obj->_id));
			}
			catch (PDOException $e)
			{
				continue;
			}
		}
	}
	catch (PDOException $e)
	{
		$db->rollBack ();
		
		$message->addWarning ('Impossível vincular Grupos ao Tipo de Usuário: '. $e->getMessage ());
	}
}

$itemId = isset ($_POST['selectTypes']) ? $_POST['selectTypes'] : '';

Log::singleton ()->add ('Nova configuração para auto-vínculo de grupos à tipos de usuários [Tipo: '. $itemId .'].');
?>