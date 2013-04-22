<?
if (!User::singleton ()->isLogged ())
	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET['assigns']))
	throw new Exception (__ ('Error! Data losted.'));

set_time_limit (0);

$useSearch = isset ($_GET['search']) && (int) $_GET['search'] ? TRUE : FALSE;

$assigns = explode (',', $_GET['assigns']);

$search = new Search ('search.xml', 'list.xml');

$search->recovery ();

$view = new View ('csv.xml', 'list.xml');

$view->setPaginate (0);

if (!$view->load ($useSearch ? $search->makeWhere () : ''))
	throw new Exception (__ ('Unable to load data!'));

$handle = tmpfile ();

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

header ('Content-Type: application/csv');
header ('Content-disposition: attachment; filename='. Business::singleton ()->getSection (Section::TCURRENT)->getName () .'_'. date ('Y-m-d_H-i-s') .'.csv');

rewind ($handle);

fpassthru ($handle);

fclose ($handle);

exit ();
?>