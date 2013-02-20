<?
try
{
	$db = Database::singleton ();
}
catch (PDOException $e)
{
	header ('Location: index.php?error='. urlencode ('Falha de conexão: '. $e->getMessage ()));
	exit ();
}
catch (Exception $e)
{
	header ('Location: index.php?error='. urlencode ($e->getMessage ()));
	exit ();
}
?>
