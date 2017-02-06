<?php

$xml = Business::singleton ()->getAction (Action::TCURRENT)->getXmlPath ();

Business::singleton ()->getAction (Action::TCURRENT)->setXmlPath (FALSE);

$view = new View ($field->getXmlPath ());

Business::singleton ()->getAction (Action::TCURRENT)->setXmlPath ($xml);

global $itemId;

if (!$view->load ($field->getColumn () ." = '". $itemId ."'"))
	throw new Exception ('Não foi possível carregar dados!');

ob_start ();

while ($view->getItem ())
{
	$line = array ();

	while ($cField = $view->getField ())
		$line [] = (trim ($cField->getLabel ()) != '' ? $cField->getLabel () .': ' : '') .'\''. Form::toText ($cField) .'\'';

	echo implode ('; ', $line) ."\n";
}

return ob_get_clean ();
