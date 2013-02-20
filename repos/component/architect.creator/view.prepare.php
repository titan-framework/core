<?
$form = new Form ('view.xml');

if (!$form->load ($itemId))
	throw new Exception ('Não foi possível carregar dados do area!');

/*$menu->addPrint ();
$menu->add ('editarArea', 'Editar', $itemId, $section->getName ());
$menu->add ('apagarArea', 'Apagar', $itemId, $section->getName ());
$menu->add ('listarArea', 'Voltar', 0, $section->getName ());*/
?>