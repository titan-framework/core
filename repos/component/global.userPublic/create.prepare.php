<?php
$form = Form::singleton ('create.xml', 'all.xml');

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Save'), 'save.png', "saveForm ('". $form->getFile () ."', 'form_". $form->getAssign () ."', '". $itemId ."');");
$menu->add ($form->goToAction ('cancel')->getName (), __ ('Cancel'), 0, $section->getName (), 'close.png');
?>