<?
$search = new Search ('search.xml', 'list.xml');

$search->recovery ();

if (!User::singleton ()->hasPermission ('_VIEW_ALL_'))
	$search->addBlock ('_ARCHITECT_', User::singleton ()->getId ());

$view = new View ('list.xml');

if (!$view->load ($search->makeWhere ()))
	throw new Exception ('Não foi possível carregar dados!');
?>