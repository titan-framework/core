<?
$form =& Form::singleton ('alert.xml');

$action = $form->goToAction ('fail');

$commitId = isset ($_POST['commitId']) ? $_POST['commitId'] : 0;

$business = Business::singleton ();

$arrayMain = array ();

while ($auxSection = $business->getSection ())
	$arrayMain = array_merge ($arrayMain, Mail::parseMail ($auxSection->getName ()));

$array = array ();
foreach ($_POST as $key => $trash)
	if (array_key_exists ($key, $arrayMain))
		$array [] = $key;

try
{
	$db = Database::singleton ();
	
	$db->beginTransaction ();
	
	$db->exec ("DELETE FROM _mail WHERE _group = '". $commitId ."'");
	
	$sth = $db->prepare ("INSERT INTO _mail (_name, _group) VALUES (:mail, '". $commitId ."')");
	
	foreach ($array as $trash => $mail)
		$sth->execute (array (':mail' => $mail));
	
	$db->commit ();
	
	$message->addMessage ('Alertas vinculados com sucesso ao grupo.');
	
	$action = $form->goToAction ('success');
}
catch (PDOException $e)
{
	$db->rollBack ();
	
	$message->addWarning ('Impossível vincular alertas ao grupo: '. $e->getMessage ());
}

Log::singleton ()->add ('Nova configuração para alertas vinculados a grupos de usuários.');
?>