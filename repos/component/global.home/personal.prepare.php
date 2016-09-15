<?php
$form =& Form::singleton ('../'. $user->getType ()->getName () .'/'. $user->getType ()->getModify ());

$itemId = $user->getId ();

if (!$form->load ($itemId))
	throw new Exception ('Não foi possível carregar os seus dados cadastrais!');

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Save'), 'save.png', "saveForm ('". $form->getFile () ."', 'form_". $form->getAssign () ."', '". $user->getId () ."');");
$menu->add ($form->goToAction ('cancel')->getName (), __ ('Cancel'), 0, $section->getName (), 'titan.php?target=loadFile&file=interface/menu/close.png');
?>