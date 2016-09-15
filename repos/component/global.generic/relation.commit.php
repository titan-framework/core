<?php
$form =& Form::singleton ('relation.xml');

$action = $form->goToAction ('fail');

if ($itemId)
{
	if (isset ($_POST['selectFor']))
		$arrayForSave = $_POST['selectFor'];
	else
		$arrayForSave = array ();

	try
	{
		$db = Database::singleton ();

		$db->beginTransaction ();

		$db->exec ("DELETE FROM ". $form->getTable () ." WHERE ". $form->getField ('_REL_FOR_')->getColumn () ." = '". $itemId ."'");

		$sth = $db->prepare ("INSERT INTO ". $form->getTable () ." (". $form->getField ('_REL_FROM_')->getColumn () .", ". $form->getField ('_REL_FOR_')->getColumn () .") VALUES (:rel, '". $itemId ."')");

		foreach ($arrayForSave as $trash => $value)
			$sth->execute (array (':rel' => $value));

		$db->commit ();

		$message->addMessage (__ ('Items linked with success!'));

		$user->setGroups ();
	}
	catch (PDOException $e)
	{
		$db->rollBack ();

		$message->addWarning (__ ('Unable link items: [1]', $e->getMessage ()));
	}
}

$itemId = $_POST['selectItem'];
?>