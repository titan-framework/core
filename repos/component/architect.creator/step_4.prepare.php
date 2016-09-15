<?php
if (isset ($_SESSION['UNIX_NAME']) && isset ($_SESSION['DBH_'. $_SESSION['UNIX_NAME']]))
	$itemId = $_SESSION['UNIX_NAME'];
else
	throw new Exception ('Houve perda de variáveis.');

$menu =& Menu::singleton ();
$menu->addJavaScript ('Preview do Sistema', 'view.png', "openPopup ('instance/". $itemId ."/titan.php?target=login', 'popup_". $itemId ."')");
?>