<pre>
<?
set_time_limit (0);
ini_set ('memory_limit', '-1');

require dirname (__FILE__) . DIRECTORY_SEPARATOR .'function.php';

$configure = 'configure'. DIRECTORY_SEPARATOR .'mobile-source.xml';

if (!file_exists ($configure))
	die ('You need create configuration file to usage ['. $configure .']! See sample at folder tool in Titan repository.');

$xml = new Xml ($configure);

$array = $xml->getArray ();

if (!isset ($array ['mobile-source'][0]['application']))
	die ('The tag &lt;mobile-source&gt;&lt;/mobile-source&gt; has not found in XML ['. $configure .']!');

$cache = Instance::singleton ()->getCachePath () .'mobile'. DIRECTORY_SEPARATOR;

if (!file_exists ($cache) && !@mkdir ($cache, 0777))
	die ('Impossible to create folder ['. $cache .'].');

if (!file_exists ($cache .'.htaccess') && !file_put_contents ($cache .'.htaccess', 'deny from all'))
	die ('Impossible to enhance security for folder ['. $cache .'].');

foreach ($array ['mobile-source'][0]['application'] as $trash => $app)
	foreach ($app ['entity'] as $trash => $params)
		if (strtoupper (trim ($params ['active'])) == 'TRUE')
		{
			echo $app ['name'] .": Generating for ". $params ['model'] ."...\n\n";
			
			generate ($app ['name'], $app ['package'], $params ['section'], $params ['model'], $params ['xml'], $params ['table'], $params ['assets']);
			
			echo "\n";
		}

echo "All done!";
?>
</pre>