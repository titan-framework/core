<?
$business = Business::singleton ();

$arrayMain = array ();

$arrayMail = array ();

while ($auxSection = $business->getSection ())
{
	$arrayMain [$auxSection->getName ()] = Mail::parseMail ($auxSection->getName ());
	
	$arrayMain [$auxSection->getName ()]['_TITAN_SECTION_'] = $auxSection->getLabel ();
}

$db = Database::singleton ();

$sth = $db->prepare ("SELECT _name FROM _mail WHERE _group = '". $itemId ."'");

$sth->execute ();

$arrayHas = array ();

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$arrayHas [$obj->_name] = $obj->_name;

$form =& Form::singleton ('alert.xml');

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Save'), 'titan.php?target=loadFile&file=interface/menu/save.png', "document.getElementById ('form_". $form->getAssign () ."').submit ();");
$menu->add ($form->goToAction ('cancel')->getName (), __ ('List Groups'), 0, $section->getName (), 'titan.php?target=loadFile&file=interface/menu/list.png');
?>