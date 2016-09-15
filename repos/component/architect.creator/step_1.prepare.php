<?php
if (!isset ($itemId) || !$itemId || trim ($itemId) == '')
	if (isset ($_SESSION['UNIX_NAME']))
		$itemId = $_SESSION['UNIX_NAME'];
	else
		throw new Exception ('Houve perda de variáveis.');
else
	$_SESSION['UNIX_NAME'] = $itemId;
	

$file = 'instance/'. $itemId .'/configure/titan.xml';

if (!file_exists ($file))
	throw new Exception ('Arquivos da instância não encontrados! ['. $file .']');

$xml = new Xml ($file);
		
$array = $xml->getArray ();

if (!isset ($array ['titan-configuration'][0]))
	throw new Exception ('A tag &lt;titan-configuration&gt;&lt;/titan-configuration&gt; não existe no arquivo ['. $file .'].');

if (!isset ($array ['titan-configuration'][0]['database'][0]))
	throw new Exception ('A tag &lt;database /&gt; não existe no arquivo ['. $file .'].');

$array ['main'] 	= $array ['titan-configuration'][0];
$array ['database'] = $array ['main']['database'][0];
$array ['security'] = $array ['main']['security'][0];
$array ['search'] 	= $array ['main']['search'][0];
$array ['archive'] 	= $array ['main']['archive'][0];
$array ['business'] = $array ['main']['business-layer'][0];
$array ['skin'] 	= $array ['main']['skin'][0];
$array ['mail'] 	= $array ['main']['mail'][0];

unset ($array ['main']['database']);
unset ($array ['main']['security']);
unset ($array ['main']['search']);
unset ($array ['main']['archive']);
unset ($array ['main']['business-layer']);
unset ($array ['main']['skin']);
unset ($array ['main']['mail']);

$ajax = new Ajax ();

if (!$ajax->verifyDB ($itemId, $array ['database'], TRUE))
	$message->addWarning ('Impossível conectar no Banco de Dados da instância.');

$_SESSION['UNIX_NAME'] = $itemId;

$db = Database::singleton ();

$sth = $db->prepare ("SELECT _logo FROM _instance WHERE _unix = '". $itemId ."'");

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

$logoId = $obj && $obj->_logo ? $obj->_logo : 0;

$menu =& Menu::singleton ();
$menu->addJavaScript ('Preview do Sistema', 'view.png', "openPopup ('instance/". $itemId ."/titan.php?target=login', 'popup_". $itemId ."');");
?>