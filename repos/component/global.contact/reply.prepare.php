<?
$form =& Form::singleton ('edit.xml', 'all.xml');

if (!$form->load ($itemId))
	throw new Exception ('Não foi possível carregar os dados do item!');

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Save'), 'save.png', "saveForm ('". $form->getFile () ."', 'form_". $form->getAssign () ."', '". $itemId ."');");
$menu->addJavaScript ('Salvar e Enviar Resposta', 'email.png', "sendResponse ('". $form->getFile () ."', 'form_". $form->getAssign () ."', '". $itemId ."');");
$menu->add ($form->goToAction ('cancel')->getName (), __ ('Cancel'), 0, $section->getName (), 'close.png');
?>