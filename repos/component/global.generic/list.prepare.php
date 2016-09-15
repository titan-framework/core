<?php
$search = new Search ('search.xml', 'list.xml');

$search->recovery ();

$view = new View ('list.xml');

if (!$view->load ($search->makeWhere ()))
	throw new Exception (__ ('Unable to load data!'));
?>