<?php
ldapUpdate ($itemId);

$form =& Form::singleton ('edit.xml', 'all.xml');

if (!$form->load ($itemId))
	throw new Exception (__('Unable to load the data of the item!'));

$menu =& Menu::singleton ();
$menu->addJavaScript (__('Save'), 'save.png', "saveForm ('". $form->getFile () ."', 'form_". $form->getAssign () ."', '". $itemId ."');");
$menu->add ($form->goToAction ('cancel')->getName (), __('Cancel'), 0, $section->getName (), 'titan.php?target=loadFile&file=interface/menu/close.png');
?>