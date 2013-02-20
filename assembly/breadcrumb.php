<?
$_OUTPUT ['BREADCRUMB'] = '';
$_OUTPUT ['MENU-POSITION'] = '';

try
{
	if (!isset ($section) || !isset ($action) || !isset ($action))
		throw new Exception ('Seção ou Ação inválida!');
	
	$menuPosition = array ();
	
	$_OUTPUT ['BREADCRUMB'] = getBreadPath ($section);
	
	$_OUTPUT ['BREADCRUMB'] = substr ($_OUTPUT ['BREADCRUMB'], 0, -9);
	
	array_shift ($menuPosition);
	$menuPosition = array_reverse ($menuPosition);
	$last = '';
	
	foreach ($menuPosition as $trash => $next)
	{
		$_OUTPUT ['MENU-POSITION'] .= "slideMenu ('". $last ."', '". $next ."', 200);";
		$last = $next;
	}
}
catch (PDOException $e)
{
	$message->addWarning ($e->getMessage ());
}
catch (Exception $e)
{
	$message->addWarning ('Erro crítico ao carregar menu: '. $e->getMessage ());
}
?>