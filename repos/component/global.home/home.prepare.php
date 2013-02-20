<?
if (Alert::isActive ())
{
	$lSection = Business::singleton ()->getSection (Section::TCURRENT);
	
	$needUpdateAfter = $lSection->getDirective ('_NEED_UPDATE_AFTER_DAYS_');
	
	if (is_numeric ($needUpdateAfter) && (int) $needUpdateAfter)
	{
		$query = Database::singleton ()->query ("SELECT count(*) FROM _user WHERE _id = '". User::singleton ()->getId () ."' AND _update_date + interval '". $needUpdateAfter ." days' < now()");
		
		if ((int) $query->fetchColumn ())
		{
			$updateAction = FALSE;
			
			while ($lAction = $lSection->getAction ())
				if ($lAction->getEngine () == 'personal')
				{
					$updateAction = $lAction->getName ();
					
					break;
				}
			
			if ($updateAction !== FALSE)
				Alert::add ('_UPDATE_PROFILE_', User::singleton ()->getId (), User::singleton ()->getId (), 
							array ('[DAYS]' => $needUpdateAfter, '[SECTION]' => $lSection->getName (), '[ACTION]' => $updateAction));
		}
	}
}
?>