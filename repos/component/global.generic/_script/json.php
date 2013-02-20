<?
/* Under development! Do not use in production. */
error_reporting (0);

header ('Content-Type: application/json');

$view = new View ('json.xml', 'list.xml');

$view->setPaginate (5);

if (!$view->load ())
    throw new Exception (__ ('Unable to load data!'));

$lines = array ();

while ($view->getItem ())
{
    $itemId = $view->getId ();
  
    $aux = array ();
  
    while ($field = $view->getField ())
         $aux [$field->getAssign ()] = Form::toText ($field);
	
    $lines [] = $aux;
}

echo '{ "TitanJsonResult": '. json_encode ($lines) .'}';
?>