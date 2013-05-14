<?
if (!User::singleton ()->isLogged ())
	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET['assigns']))
	throw new Exception (__ ('Error! Data losted.'));

set_time_limit (0);
ini_set ('memory_limit', -1);

$useSearch = isset ($_GET['search']) && (int) $_GET['search'] ? TRUE : FALSE;

$assigns = explode (',', $_GET['assigns']);

if ($useSearch)
{
	$search = new Search ('search.xml', 'list.xml');

	$search->recovery ();
	
	$where = $search->makeWhere ();
	
	$where .= trim ($where) == '' ? "_type = '". $section->getName () ."' AND _deleted = '0'" : " AND _type = '". $section->getName () ."' AND _deleted = '0'";
}
else
	$where = "_type = '". $section->getName () ."' AND _deleted = '0'";

$view = new View ('csv.xml', 'list.xml');

$view->setPaginate (0);

if (!$view->load ($where))
	throw new Exception (__ ('Unable to load data!'));

set_error_handler ('logPhpError');

header ('Content-Type: application/csv');
header ('Content-disposition: attachment; filename='. Business::singleton ()->getSection (Section::TCURRENT)->getName () .'_'. date ('Y-m-d_H-i-s') .'.csv');
header ('Pragma: no-cache');
header ('Expires: 0');

$handle = fopen ('php://output', 'w');

$aux = array ();

while ($field = $view->getField ())
	if (in_array ($field->getAssign (), $assigns))
		$aux [] = utf8_decode ($field->getLabel ());

fputcsv ($handle, $aux, ';', '"');

while ($view->getItem ())
{
	$itemId = $view->getId ();
	
	$aux = array ();
	
	while ($field = $view->getField ())
		if (in_array ($field->getAssign (), $assigns))
			$aux [] = str_replace (array ("\n", "\r", "\t"), array (' ', '', ' '), utf8_decode (trim (Form::toText ($field))));
	
	fputcsv ($handle, $aux, ';', '"');
}

fclose ($handle);

restore_error_handler ();

exit ();
?>