<?
ob_start ();

if (!$view->load ())
	die ('CRITICAL > Impossible to load items from table ['. $view->getTable () .']!');

while ($view->getItem ())
{
	if ($useCode)
	{
		$assetsColumns = array ($code);
		$assetsValues = array ('"'. $view->getCode () .'"');
	}
	else
	{
		$assetsColumns = array ($primary);
		$assetsValues = array ($view->getId ());
	}
	
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
				$assetsValues [] = $field->isEmpty () ? 'NULL' : (string) number_format ($field->getValue (), $field->getPrecision (), '.', '');
				break;
				
			case 'Long':
				$assetsValues [] = $field->isEmpty () ? 'NULL' : (string) $field->getValue ();
				break;
			
			default:
				$assetsValues [] = '"'. ApiEntity::toApi ($field) .'"';
		}
	}
	
	echo "INSERT INTO ". $table ." (". implode (", ", $assetsColumns) .") VALUES (". implode (", ", $assetsValues) .");\n";
}

return ob_get_clean ();
?>