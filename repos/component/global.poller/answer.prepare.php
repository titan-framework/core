<?
$form =& Form::singleton ('create.xml', 'all.xml');

$db = Database::singleton ();

$sql = "SELECT _label FROM ". $form->getTable () ."_answer WHERE _poller = '". $itemId ."' ORDER BY _order";
								
$sth = $db->prepare ($sql);

$sth->execute ();

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Save'), 'save.png', "Enviar ();");
$menu->add ($form->goToAction ('cancel')->getName (), __ ('Cancel'), 0, $section->getName (), 'titan.php?target=loadFile&file=interface/menu/close.png');
?>