<?
$form =& Form::singleton ('view.xml', 'all.xml');

if (!$form->load ($itemId))
	throw new Exception ('Não foi possível carregar os dados do item!');
?>