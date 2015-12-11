<?
try
{
	if(!isset ($_SESSION['user']))
		throw new Exception ();
	
	$user =& User::singleton ();
	
	if (!$user->isLogged ())
		throw new Exception (__ ('You has desconnected for security reasons! You user is long time inactive.'));
}
catch (Exception $e)
{
	?>
	<html><body onLoad="JavaScript: parent.document.location='titan.php?target=logoff<?= $e->getMessage () != '' ? '&error=' . urlencode ($e->getMessage ()) : '' ?><?= trim ($_SERVER['QUERY_STRING']) != '' ? '&url='. urlencode (str_replace ('target=body', '', $_SERVER['QUERY_STRING'])) : '' ?>';"></body></html>
	<?
	
	exit ();
}
catch (PDOException $e)
{
	?>
	<html><body onLoad="JavaScript: parent.document.location='titan.php?target=logoff&error=<?= urlencode (__ ('Query execution error: [1]', $e->getMessage ())) ?>';"></body></html>
	<?
	
	exit ();
}
?>