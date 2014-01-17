<?
$import = $base .'assets'. DIRECTORY_SEPARATOR . $table .'.sql';

if (!file_exists ($import))
	die ('CRITICAL > Do not exists correspondent assets file ['. $import .']!');

$file = $base .'assets'. DIRECTORY_SEPARATOR . $appName .'.db';

try
{
	$dbh = new PDO ('sqlite:'. $file);
	
	$dbh->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
	die ('CRITICAL > Impossible to open or generate DB at ['. $base . $appName .'.db]: '. $e->getMessage ());
}

$columns = array ();
foreach ($fields as $trash => $obj)
	$columns [] = $obj->json ." ". $obj->db;

$sql = "CREATE TABLE ". $table ." (". implode (", ", $columns) .");";

try
{
	$dbh->exec ("DROP TABLE IF EXISTS android_metadata;");
	
	$dbh->exec ("CREATE TABLE android_metadata (\"locale\" TEXT DEFAULT 'en_US');");
	
	$dbh->exec ("INSERT INTO android_metadata VALUES ('en_US');");
	
	$dbh->exec ("DROP TABLE IF EXISTS ". $table .";");
	
	$dbh->exec ($sql);
	
	$dbh->exec (file_get_contents ($import));
	
	$dbh = NULL;
}
catch (PDOException $e)
{
	die ('CRITICAL > Impossible to import dump to SQLite database: '. $e->getMessage ());
}

echo "SUCCESS > Table [". $table ."] has been generated at SQLite database! [". $file ."] \n";