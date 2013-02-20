<?
$search = new Search ('search.xml', 'list.xml');

$search->recovery ();

$view = new View ('csv.xml', 'list.xml');

$view->setPaginate (0);

if (!$view->load ($search->makeWhere ()))
	throw new Exception (__ ('Unable to load data!'));

header ('Content-Type: text/csv');
header ('Content-Disposition: inline; filename=' . fileName (Business::singleton ()->getSection (Section::TCURRENT)->getLabel ()) .'_'. date ('Ymd') .'.csv');

$array = array ();

while ($field = $view->getField ())
	$array [] = '"'. addslashes (Form::toLabel ($field)) .'"';

echo implode (";", $array) ."\n";

while ($view->getItem ())
{
	$array = array ();
	
	$itemId = $view->getId ();
	
	while ($field = $view->getField ()) 
		$array [] = '"'. addslashes (Form::toText ($field)) .'"';
	
	echo implode (";", $array) ."\n";
}
?>