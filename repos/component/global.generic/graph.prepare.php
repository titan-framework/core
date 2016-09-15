<?php
$search = new Search ('filter.xml', 'search.xml', 'graph.xml', 'list.xml');

$search->recovery ();

$graph = new Graph ('graph.xml', 'all.xml', 'view.xml');

if ($type = Business::singleton ()->getSection (Section::TCURRENT)->getDirective ('_GRAPH_TYPE_'))
	$graph->setType ($type);
?>