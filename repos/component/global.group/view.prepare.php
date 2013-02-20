<?
$form =& Form::singleton ('create.xml', 'all.xml');

if (!$form->load ($itemId))
	throw new Exception ('Não foi possível carregar os dados do grupo!');
?>