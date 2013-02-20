<?
$view = new View ('rss.xml', 'list.xml');

if (!$view->load ("_type = '". $section->getName () ."' AND _deleted = '0'"))
	throw new Exception (__('Unable to load data!'));
?>