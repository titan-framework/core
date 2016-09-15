<?php
ldapUpdate ();

$search = new Search ('search.xml', 'list.xml');

$search->recovery ();

$where = $search->makeWhere ();

$where .= trim ($where) == '' ? "_type = '". $section->getName () ."' AND _deleted = '0'" : " AND _type = '". $section->getName () ."' AND _deleted = '0'";

$view = new View ('list.xml');

if (!$view->load ($where))
	throw new Exception (__('Unable to load data!'));
?>