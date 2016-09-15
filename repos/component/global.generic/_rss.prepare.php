<?php
$view = new View ('rss.xml', 'list.xml');

if (!$view->load ())
	throw new Exception (__ ('Unable to load data!'));
?>