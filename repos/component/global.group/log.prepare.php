<?php
$search = new SearchLog ('log.xml');

$search->recovery ();

$view = new ViewLog ('log.xml');

if (!$view->load ($search->makeWhere ()))
	throw new Exception ('Não foi possível carregar dados!');

$log = Log::singleton ();

$test = $log->loadActivities ();
?>