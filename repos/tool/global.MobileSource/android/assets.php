<?
ob_start ();

if (!$view->load ())
	toLog ('Impossible to load items from table ['. $view->getTable () .']!');

while ($view->getItem ())
{
	$assetsColumns = array ($primary);
	$assetsValues = array ($view->getId ());
	
	while ($field = $view->getField ())
	{
		$assetsColumns [] = $field->getApiColumn ();
		
		switch (translateType ($field))
		{
			case 'Boolean':
				$assetsValues [] = $field->getValue () ? '1' : '0';
				break;
			
			case 'Date':
				$assetsValues [] = $field->isEmpty () ? 'NULL' : (string) $field->getUnixTime ();
				break;
			
			case 'Double':
			case 'Long':
				$assetsValues [] = $field->isEmpty () ? 'NULL' : (string) $field->getValue ();
				break;
			
			default:
				$assetsValues [] = '"'. ApiEntity::toApi ($field) .'"';
		}
	}
	
	echo "INSERT INTO ". $table ." (". implode (", ", $assetsColumns) .") VALUES (". implode (", ", $assetsValues) .")\n";
}

return ob_get_clean ();
?>