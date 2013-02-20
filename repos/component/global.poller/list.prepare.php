<?
$search = new Search ('search.xml', 'list.xml');

$search->recovery ();

$view = new View ('list.xml');

if (!$view->load ($search->makeWhere ()))
	throw new Exception ('Não foi possível carregar dados!');
?>