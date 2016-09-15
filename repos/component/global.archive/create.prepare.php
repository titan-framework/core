<?php
$form =& Form::singleton ('create.xml');

$menu =& Menu::singleton ();
$menu->addJavaScript ( __ ('Save'), 'titan.php?target=loadFile&file=interface/menu/save.png', "document.getElementById ('form_". $form->getAssign () ."').submit ()");
$menu->add ($form->goToAction ('cancel')->getName (), __ ('Cancel'), 0, $section->getName (), 'titan.php?target=loadFile&file=interface/menu/close.png');
?>