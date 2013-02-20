<?
$_OUTPUT ['SECTION_MENU'] = '';
$_OUTPUT ['SECTION'] = '';

try
{
	ob_start ();
	
	if (!isset ($section) || !isset ($action))
		throw new Exception ('Seção ou Ação inválida!');
	
	$action->generateMenu ();
	
	if (file_exists ($action->getFullPathTo (Action::PREPARE)))
		include $action->getFullPathTo (Action::PREPARE);
	
	while ($item = Menu::singleton ()->get ())
		echo $item;
	
	$_OUTPUT ['SECTION_MENU'] = '<ul>'. ob_get_clean () .'</ul>';
	
	ob_start ();
	
	include $action->getFullPathTo (Action::VIEW);
	
	$_OUTPUT ['SECTION'] = ob_get_clean ();
}
catch (PDOException $e)
{
	ob_end_clean ();
	
	$message->addWarning ($e->getMessage ());
}
catch (Exception $e)
{
	ob_end_clean ();
	
	$message->addWarning ($e->getMessage ());
}
?>