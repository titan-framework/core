<?php
$business = Business::singleton ();

$arrayMain = array ();

$arrayPermission = array ();

while ($auxSection = $business->getSection ())
{
	$arrayMain [$auxSection->getName ()]['SECTION'] = substr (getBreadPath ($auxSection, FALSE, FALSE), 0, -9);
	$arrayMain [$auxSection->getName ()]['ACTION'] = $auxSection->getAction (Action::TDEFAULT)->getName ();
	
	while ($auxAction = $auxSection->getAction ())
		$arrayMain [$auxSection->getName ()][$auxAction->getName ()] = $auxAction->getLabel ();
	
	while ($auxPermission = $auxSection->getPermission ())
		$arrayPermission [$auxSection->getName ()][$auxPermission ['name']] = translate ($auxPermission ['label']);
}

$db = Database::singleton ();

$sth = $db->prepare ("SELECT _name FROM _permission WHERE _group = '". $itemId ."'");

$sth->execute ();

$arrayHas = array ();

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$arrayHas [$obj->_name] = $obj->_name;

$form =& Form::singleton ('permission.xml');

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Save'), 'titan.php?target=loadFile&file=interface/menu/save.png', "document.getElementById ('form_". $form->getAssign () ."').submit ();");
$menu->add ($form->goToAction ('cancel')->getName (), __ ('List Groups'), 0, $section->getName (), 'titan.php?target=loadFile&file=interface/menu/list.png');
?>