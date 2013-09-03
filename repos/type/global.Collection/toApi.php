<?
$xml = Business::singleton ()->getAction (Action::TCURRENT)->getXmlPath ();

Business::singleton ()->getAction (Action::TCURRENT)->setXmlPath (FALSE);

$view = new View ($field->getXmlPath ());

Business::singleton ()->getAction (Action::TCURRENT)->setXmlPath ($xml);

global $itemId;

if (!$view->load ($field->getColumn () ." = '". $itemId ."'"))
	throw new Exception ('Não foi possível carregar dados!');

$buffer = array ();

while ($view->getItem ())
{
	$line = array ();
	
	while ($cField = $view->getField ())
	{
		$str = Form::toText ($cField);
		
		if (trim ($str) == '')
			continue;
		
		$line [] = str_replace (';', ',', $str);
	}
	
	$buffer [] = implode (' - ', $line);
}

return implode (';', $buffer);
?>