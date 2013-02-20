<?
$search = new Search ('filter.xml', 'search.xml', 'graph.xml', 'list.xml');

$search->recovery ();

$where = $search->makeWhere ();

$where .= trim ($where) == '' ? "_type = '". $section->getName () ."' AND _deleted = '0'" : " AND _type = '". $section->getName () ."' AND _deleted = '0'";

$graph = new Graph ('graph.xml', 'all.xml', 'view.xml');

if ($type = Business::singleton ()->getSection (Section::TCURRENT)->getDirective ('_GRAPH_TYPE_'))
	$graph->setType ($type);
?>