<?
if (!$itemId)
	throw new Exception ('There was lost of variables!');

$search = new VersionSearch ('version.xml', 'search.xml', 'list.xml');

$search->recovery ();

$view = new VersionView ('version.xml', 'list.xml');

$where = $search->makeWhere ();

$where = trim ($where) != '' ? $where ." AND " : "";

$where = $where . $view->getTable () .'.'. $view->getVersionedPrimary () ." = '". $itemId ."'";

$flag = Version::singleton ()->hasControl ($view->getVersionedTable ());

if (!$flag)
	Menu::singleton ()->remove (Menu::SEARCH);
elseif (!$view->load ($where))
	throw new Exception (__('Unable to load the data!'));
?>