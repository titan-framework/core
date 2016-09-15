<?php
$view = new View ('rss.xml', 'list.xml');

if (!$view->load ())
	throw new Exception ('Não foi possível carregar dados!');
?>