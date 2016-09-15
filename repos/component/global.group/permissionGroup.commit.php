<?php
$form =& Form::singleton ('permission.xml');

$action = $form->goToAction ('fail');

$commitId = isset ($_POST['commitId']) ? $_POST['commitId'] : 0;

$business = Business::singleton ();

$arrayMain = array ();

$arrayPermission = array ();

while ($auxSection = $business->getSection ())
{
	while ($auxAction = $auxSection->getAction ())
		$arrayMain [$auxSection->getName ()][] = $auxAction->getName ();
	
	while ($auxPermission = $auxSection->getPermission ())
		$arrayPermission [$auxSection->getName ()][] = $auxPermission ['name'];
}

//die (print_r ($_POST));

$array = array ();
foreach ($arrayMain as $idSection => $arraySection)
{
	if (!isset ($_POST ['ACCESS_SECTION_'. $idSection]) && !isset ($_POST ['ACCESS_SECTION_'. str_replace ('.', '_', $idSection)]))
		continue;
	
	$array [] = 'ACCESS_SECTION_'. $idSection;
	
	foreach ($arraySection as $trash => $idAction)
		if (isset ($_POST ['ACCESS_ACTION_'. $idSection .'_'. $idAction]) || isset ($_POST ['ACCESS_ACTION_'. str_replace ('.', '_', $idSection) .'_'. $idAction]))
			$array [] = 'ACCESS_ACTION_'. $idSection .'_'. $idAction;
	
	if (array_key_exists ($idSection, $arrayPermission))
		foreach ($arrayPermission [$idSection] as $trash => $permission)
			if (isset ($_POST ['PERMISSION_'. $idSection .'_'. $permission]) || isset ($_POST ['PERMISSION_'. str_replace ('.', '_', $idSection) .'_'. $permission]))
				$array [] = 'PERMISSION_'. $idSection .'_'. $permission;
}

try
{
	$db = Database::singleton ();
	
	$db->beginTransaction ();
	
	$db->exec ("DELETE FROM _permission WHERE _group = '". $commitId ."'");
	
	$sth = $db->prepare ("INSERT INTO _permission (_name, _group) VALUES (:permission, '". $commitId ."')");
	
	foreach ($array as $trash => $permission)
		$sth->execute (array (':permission' => $permission));
	
	$db->commit ();
	
	$message->addMessage ('Permissões vinculadas com sucesso.');
	
	$user->setGroups ();
	
	$action = $form->goToAction ('success');
}
catch (PDOException $e)
{
	$db->rollBack ();
	
	$message->addWarning ('Impossível vincular permissões ao grupo: '. $e->getMessage ());
}

Log::singleton ()->add ('Nova configuração para permissões vinculadas a grupos de usuários [Grupo: '. $commitId .'].');
?>