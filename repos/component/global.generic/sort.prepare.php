<?
$view = new View ('sort.xml', 'list.xml');

if (!$view->load ())
	throw new Exception (__ ('Unable to load data!'));

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Save'), 'titan.php?target=loadFile&file=interface/menu/save.png', "saveSort ();");
?>