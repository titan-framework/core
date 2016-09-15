<pre>
<?php
$instance = Instance::singleton ();

session_name ($instance->getSession () .'_PUBLIC_');

session_start ();

foreach ($instance->getTypes () as $type => $path)
	require_once $path . $type .'.php';

define ('SIZE', 0);
define ('FILE', 1);

$db = Database::singleton ();

$tables = array ();

while ($section = Business::singleton ()->getSection ())
{
	while ($action = $section->getAction ())
	{
		$path = "section/". $section->getName () ."/";
		
		$file1 = $path . $action->getXmlPath ();
		
		$file2 = $path . $action->getEngine () .'.xml';
		
		try
		{
			$dbMaker = new DatabaseMaker ($file1, $file2);
			
			$table = $dbMaker->getTable ();
			$size  = $dbMaker->getSize ();
			$file  = $dbMaker->getFile ();
			
			if (!array_key_exists ($table, $tables) || $size > $tables [$table][SIZE])
				$tables [$table] = array ($size, $file);
		}
		catch (Exception $e)
		{
			continue;
		}
	}
	
	try
	{
		$dbMaker = new DatabaseMaker ('section/'. $section->getName () .'/all.xml');
		
		$table = $dbMaker->getTable ();
		$size  = $dbMaker->getSize ();
		$file  = $dbMaker->getFile ();
		
		if (!array_key_exists ($table, $tables) || $size > $tables [$table][SIZE])
			$tables [$table] = array ($size, $file);
	}
	catch (Exception $e)
	{
		continue;
	}
}

$schemas = array ($db->getSchema ());

foreach ($tables as $table => $trash)
{	
	$aux = explode ('.', $table);
	
	if (in_array ($aux [0], $schemas) || trim ($aux [0]) == '')
		continue;
	
	$schemas [] = $aux [0];
	
	echo "CREATE SCHEMA ". $aux [0] ."; \n";
}

echo "\n";

foreach ($tables as $table => $array)
{
	//if (tableExists ($table))
	//	continue;
	
	$dbMaker = new DatabaseMaker ($array [FILE]);
	
	echo $dbMaker->makeTable ();	
}
?>
</pre>