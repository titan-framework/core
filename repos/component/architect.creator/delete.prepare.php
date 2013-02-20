<?
$form = new Form ('view.xml');

if (!$form->load ($itemId))
	throw new Exception ('Não foi possível carregar dados!');

//$menu->add ('listarArea', 'Voltar', 0, $section->getName ());

$goTo = 'listarArea';
?>